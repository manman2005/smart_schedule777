<?php
// ไฟล์: htdocs/ai_action.php
// เวอร์ชัน: ULTIMATE HYBRID (หา Model อัตโนมัติ + เชื่อมต่อแบบถึกทน + จัดรูปแบบตารางเรียน + แจ้งเตือน Quota ภาษาไทย)

// 1. ตั้งค่าระบบให้ทำงานต่อเนื่อง ไม่ตัดจบง่ายๆ
ignore_user_abort(true); 
set_time_limit(300); // ให้เวลา 5 นาที

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// 🔑 API KEY (ใช้คีย์เดิมของคุณ)
$apiKey = 'AIzaSyBD65NOBcTvE28iIxtUQvuDcBwMKwypIYU'; 

$dataDebug = [];

// 2. เชื่อมต่อฐานข้อมูล
$dbPath = 'config/db.php';
if (!file_exists($dbPath)) { $dbPath = '../config/db.php'; }
if (!file_exists($dbPath)) { $dbPath = '../../config/db.php'; }

$conn = null;
if (file_exists($dbPath)) {
    ob_start(); include $dbPath; ob_end_clean();
    if (isset($pdo) && $pdo instanceof PDO) {
        $conn = $pdo;
        $conn->exec("set names utf8mb4");
        try { $conn->exec("SET SQL_BIG_SELECTS=1"); } catch (Exception $e) {}
    }
}

// 3. ฟังก์ชันดึงข้อมูล (Schedule Formatting) - คงไว้เหมือนเดิมเพื่อให้ AI ตอบคำถามตารางเรียนได้
function getAllContext($conn, &$dataDebug) {
    if (!$conn) return "";
    $context = "";
    
    try {
        if ($conn instanceof PDO) {
            
            // --- Schedule ---
            $sql = "SELECT 
                        d.day_name, 
                        ts.tim_range, 
                        c.cla_name,
                        c.cla_year,      
                        c.cla_group_no,  
                        sch.sch_academic_year, 
                        s.sub_name, 
                        t.tea_fullname, 
                        r.roo_name
                    FROM schedule sch
                    LEFT JOIN class_groups c ON sch.cla_id = c.cla_id
                    LEFT JOIN subjects s ON sch.sub_id = s.sub_id
                    LEFT JOIN teachers t ON sch.tea_id = t.tea_id
                    LEFT JOIN rooms r ON sch.roo_id = r.roo_id
                    LEFT JOIN days d ON sch.day_id = d.day_id
                    LEFT JOIN time_slots ts ON sch.tim_id = ts.tim_id
                    ORDER BY sch.day_id ASC, sch.tim_id ASC
                    LIMIT 200"; 

            try {
                $stmt = $conn->query($sql);
                if ($stmt) {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $list = [];
                    foreach ($rows as $r) {
                        $className = $r['cla_name'] ?? '-';
                        $currentYear = !empty($r['sch_academic_year']) ? intval($r['sch_academic_year']) : 2569;
                        $admitYear = !empty($r['cla_year']) ? intval($r['cla_year']) : $currentYear;
                        $yearLevel = ($currentYear - $admitYear) + 1;
                        if ($yearLevel < 1) $yearLevel = 1; 
                        $roomNo = intval($r['cla_group_no']);
                        
                        // รูปแบบ: สสส.1/2
                        $fullClassName = "{$className}.{$yearLevel}/{$roomNo}";

                        $list[] = "🗓️ {$r['day_name']} ({$r['tim_range']}) : กลุ่ม $fullClassName เรียนวิชา {$r['sub_name']} สอนโดย {$r['tea_fullname']} ที่ห้อง {$r['roo_name']}";
                    }
                    if ($list) {
                        $context .= "📅 ตารางสอนทั้งหมด (Schedule List):\n" . implode("\n", $list) . "\n\n";
                    }
                }
            } catch (Exception $e) { }

            // --- ครู & นักเรียน ---
            try {
                $stmt = $conn->query("SELECT tea_fullname FROM teachers LIMIT 50");
                if ($stmt) {
                    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    if ($rows) $context .= "👨‍🏫 ครู: " . implode(", ", $rows) . "\n\n";
                }
                $stmt = $conn->query("SELECT stu_fullname FROM students LIMIT 50");
                if ($stmt) {
                    $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    if ($rows) $context .= "👨‍🎓 นักเรียน: " . implode(", ", $rows) . "\n\n";
                }
            } catch (Exception $e) {}
        }
    } catch (Exception $e) { }

    if ($context) {
        return "คำสั่ง: คุณคือผู้เชี่ยวชาญการจัดตารางเรียน.\n" .
               "ข้อมูลจริง (Real-time):\n" .
               "================ SYSTEM DATA ================\n" .
               $context .
               "================ END DATA ================\n" .
               "เวลาตอบให้ระบุชื่อกลุ่มเรียนแบบเต็ม (เช่น สสส.1/2)\n\n";
    }
    return "";
}

// 4. เริ่มทำงาน
$userPrompt = $_POST['prompt'] ?? '';
if (empty($userPrompt)) {
    $json = json_decode(file_get_contents('php://input'), true);
    $userPrompt = $json['prompt'] ?? '';
}
if (empty($userPrompt)) { echo json_encode(['status' => 'error', 'message' => 'No Input']); exit; }

$systemContext = getAllContext($conn, $dataDebug);
$finalPrompt = $systemContext . "คำถาม: " . $userPrompt;

// 🔥 5. ฟังก์ชันหา Model อัตโนมัติ (แก้ปัญหา Model Not Found)
function getWorkingModelName($apiKey) {
    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    $bestModel = 'gemini-1.5-flash'; // ค่า Default กันตาย

    if (isset($data['models'])) {
        foreach ($data['models'] as $m) {
            $name = str_replace('models/', '', $m['name']);
            if (isset($m['supportedGenerationMethods']) && in_array('generateContent', $m['supportedGenerationMethods'])) {
                if (strpos($name, 'audio') === false && 
                    strpos($name, 'vision') === false && 
                    strpos($name, 'embedding') === false) {
                    if (strpos($name, 'flash') !== false) {
                        return $name;
                    }
                    $bestModel = $name;
                }
            }
        }
    }
    return $bestModel;
}

// หาชื่อ Model ที่ใช้ได้จริง
ob_clean();
$modelName = getWorkingModelName($apiKey);

$url = "https://generativelanguage.googleapis.com/v1beta/models/$modelName:generateContent?key=$apiKey";

$data = [
    "contents" => [ [ "parts" => [ ["text" => $finalPrompt] ] ] ],
    "safetySettings" => [
        [ "category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_NONE" ],
        [ "category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_NONE" ],
        [ "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_NONE" ],
        [ "category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_NONE" ]
    ]
];

// 6. Auto-Retry with Robust Connection (แก้ปัญหา HTTP 0)
$maxRetries = 3;
$attempt = 0;
$httpCode = 0;
$finalResponse = "";
$curlError = "";

do {
    $attempt++;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // ตั้งค่า Network ให้ถึกทน
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);         
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); 
    
    $finalResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($finalResponse === false) {
        $curlError = curl_error($ch);
    }
    
    curl_close($ch);

    // ถ้า HTTP Code OK หรือเป็น Error ที่ไม่ใช่ Quota เต็ม (429) ให้หยุด Loop
    if ($httpCode === 200 || ($httpCode >= 400 && $httpCode != 429 && $httpCode != 503 && $httpCode != 0)) {
        break;
    }

    sleep(2); // รอ 2 วิ แล้วลองใหม่

} while ($attempt < $maxRetries);

// 7. ตรวจสอบและตอบกลับ (จุดที่แก้ไข: แจ้งเตือนภาษาไทยเมื่อ Quota เต็ม)
$json = json_decode($finalResponse, true);

// เช็ค Error 429 (Quota Exceeded) หรือคำว่า quota ในข้อความ Error
if ($httpCode == 429 || (isset($json['error']) && stripos(($json['error']['message'] ?? ''), 'quota') !== false)) {
    // ส่งข้อความแจ้งเตือนภาษาไทยที่สุภาพ
    $friendlyMessage = "⚠️ **ระบบ AI กำลังทำงานหนัก (โควต้าเต็มชั่วคราว)**\n\nตอนนี้มีการใช้งานหนาแน่น กรุณา **รอประมาณ 1 นาที** แล้วกดลองใหม่อีกครั้งครับ";
    // ส่งเป็น status: success เพื่อให้หน้าเว็บแสดงข้อความนี้ในกล่องแชท
    echo json_encode(['status' => 'success', 'answer' => $friendlyMessage]); 
    exit;
}

if ($httpCode === 200) {
    $ans = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if ($ans) {
        echo json_encode(['status' => 'success', 'answer' => $ans]);
    } else {
        echo json_encode(['status' => 'error', 'message' => "AI ไม่ตอบกลับ"]);
    }
} else {
    // แจ้ง Error อื่นๆ ที่ไม่ใช่ Quota
    if ($httpCode === 0) {
        echo json_encode(['status' => 'error', 'message' => "Connect Failed ($modelName): $curlError"]);
    } else {
        $msg = $json['error']['message'] ?? "HTTP $httpCode";
        echo json_encode(['status' => 'error', 'message' => "Error ($modelName): $msg"]);
    }
}
?>