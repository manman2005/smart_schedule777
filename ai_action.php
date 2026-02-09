<?php
// ไฟล์: smart_schedule/ai_action.php
// เวอร์ชัน: STUDENT DATA ADDED (เพิ่มการดึงข้อมูลนักเรียนเพื่อให้ AI ตอบจำนวนได้)

// 1. ตั้งค่าระบบ
ignore_user_abort(true);
set_time_limit(300); 

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// 🔑 7 API KEYS (7 Project แยก)
$apiKeys = [
    "AIzaSyDNongm703oeHUi0UMNOIOTm3TN8UkrJ9E",
    "AIzaSyAfaPMoVK5OnQ8Jo-Y1I27JTCGNZoGP4DQ",
    "AIzaSyBI6SM_KKs0PzW6oMF0bD67GN8WLhYZwyM",
    "AIzaSyBiAtWH5KqMXCYgIJiJ1bY_kjWPcSOSkgI",
    "AIzaSyAzlX7WXvi085CpFegveQaEieipvFU_JrE",
    "AIzaSyAmKZmRgIYVHVmdnhbshJPkVT6CdMmckfo",
    "AIzaSyBJFfknMkZ478YVQmFJRSeYDTf_7G5wvWw"
];

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

// 3. ฟังก์ชันดึงข้อมูล (เพิ่มส่วนดึงนักเรียนแล้ว)
function getAllContext($conn) {
    if (!$conn) return "";
    $context = "";
    
    try {
        if ($conn instanceof PDO) {
            
            // --- [ส่วนที่ 1] ดึงข้อมูลนักเรียน (ที่เพิ่มเข้ามาใหม่) ---
            try {
                // ดึงชื่อนักเรียนทั้งหมด
                $stmt = $conn->query("SELECT stu_fullname FROM students LIMIT 500");
                if ($stmt) {
                    $students = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $count = count($students);
                    if ($count > 0) {
                        $context .= "👨‍🎓 ข้อมูลนักเรียนในระบบ:\n";
                        $context .= "- จำนวนทั้งหมด: $count คน\n";
                        $context .= "- รายชื่อ: " . implode(", ", $students) . "\n\n";
                    } else {
                        $context .= "👨‍🎓 ข้อมูลนักเรียน: ยังไม่มีข้อมูลในระบบ\n\n";
                    }
                }
            } catch (Exception $e) {}

            // --- [ส่วนที่ 2] ดึงตารางเรียน ---
            $sql = "SELECT d.day_name, ts.tim_range, 
                           c.cla_name, c.cla_year, c.cla_group_no, 
                           s.sub_name, t.tea_fullname, r.roo_name,
                           sch.sch_academic_year
                    FROM schedule sch
                    LEFT JOIN class_groups c ON sch.cla_id = c.cla_id
                    LEFT JOIN subjects s ON sch.sub_id = s.sub_id
                    LEFT JOIN teachers t ON sch.tea_id = t.tea_id
                    LEFT JOIN rooms r ON sch.roo_id = r.roo_id
                    LEFT JOIN days d ON sch.day_id = d.day_id
                    LEFT JOIN time_slots ts ON sch.tim_id = ts.tim_id
                    ORDER BY sch.day_id ASC, sch.tim_id ASC
                    LIMIT 2000";

            $stmt = $conn->query($sql);
            if ($stmt) {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $list = [];
                foreach ($rows as $r) {
                    // คำนวณชั้นปี
                    $className = $r['cla_name'] ?? '-';
                    $currentYear = !empty($r['sch_academic_year']) ? intval($r['sch_academic_year']) : (date('Y') + 543);
                    $admitYear = !empty($r['cla_year']) ? intval($r['cla_year']) : $currentYear;
                    $level = ($currentYear - $admitYear) + 1;
                    if ($level < 1) $level = 1;
                    $groupNo = intval($r['cla_group_no']);
                    
                    // Format: สสส.2/1
                    $fullClassName = "{$className}.{$level}/{$groupNo}";

                    $list[] = "{$r['day_name']} {$r['tim_range']}: กลุ่ม $fullClassName เรียน {$r['sub_name']} กับ {$r['tea_fullname']} ห้อง {$r['roo_name']}";
                }
                if ($list) $context .= "📅 ตารางสอนทั้งหมด:\n" . implode("\n", $list) . "\n\n";
            }

            // --- [ส่วนที่ 3] ดึงรายชื่อครู ---
            $stmt = $conn->query("SELECT tea_fullname FROM teachers LIMIT 200");
            if ($stmt) {
                $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
                if ($rows) {
                    $teacherCount = count($rows);
                    $context .= "👨‍🏫 ครูอาจารย์ทั้งหมด ($teacherCount ท่าน): " . implode(", ", $rows) . "\n\n";
                }
            }
        }
    } catch (Exception $e) { }

    if ($context) {
        return "System Prompt: คุณคือ AI ผู้ดูแลระบบ 'CVC Smart Schedule'.\n" .
               "ข้อมูลจริง (Real-time Data):\n" .
               "----------------\n" . $context . "\n----------------\n" .
               "คำสั่ง: ตอบคำถามโดยใช้ข้อมูลข้างบนเท่านั้น ถ้าถามจำนวนนักเรียนให้ตอบตามข้อมูลที่มี\n" .
               "***สำคัญ: เวลาเรียกชื่อกลุ่มเรียน ให้ใช้รูปแบบ 'ชื่อ.ชั้นปี/ห้อง' เช่น สสส.1/1, สสส.2/1 เสมอ***\n\n";
    }
    return "";
}

// 4. รับ Input
$userPrompt = $_POST['prompt'] ?? '';
if (empty($userPrompt)) {
    $jsonInput = json_decode(file_get_contents('php://input'), true);
    $userPrompt = $jsonInput['prompt'] ?? '';
}

if (empty($userPrompt)) { 
    echo json_encode(['status' => 'error', 'message' => 'กรุณาพิมพ์ข้อความ']); 
    exit; 
}

$systemContext = getAllContext($conn);
$finalPrompt = $systemContext . "คำถาม: " . $userPrompt;

// 5. Helper Function หา Model
function getWorkingModelName($apiKey) {
    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode != 200) return 'gemini-1.5-flash';

    $data = json_decode($response, true);
    $preferred = ['gemini-1.5-flash', 'gemini-1.5-flash-latest', 'gemini-1.0-pro'];
    
    if (isset($data['models'])) {
        foreach ($preferred as $p) {
            foreach ($data['models'] as $m) {
                $name = str_replace('models/', '', $m['name']);
                if ($name === $p) return $name;
            }
        }
        foreach ($data['models'] as $m) {
            $name = str_replace('models/', '', $m['name']);
            if (strpos($name, 'flash') !== false && strpos($name, 'vision') === false) {
                return $name;
            }
        }
    }
    return 'gemini-1.5-flash';
}

// 6. Key Rotation Loop
$successResponse = null;
$debugErrors = [];

foreach ($apiKeys as $index => $currentKey) {
    $modelName = getWorkingModelName($currentKey);
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$modelName:generateContent?key=$currentKey";

    $data = [
        "contents" => [ [ "parts" => [ ["text" => $finalPrompt] ] ] ],
        "safetySettings" => [
            [ "category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_NONE" ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);          
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); 
    
    $finalResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $json = json_decode($finalResponse, true);
        if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
            $successResponse = $json['candidates'][0]['content']['parts'][0]['text'];
            break;
        }
    }
    
    $debugErrors[] = "Key#".($index+1).": $httpCode";
    if ($httpCode == 429 || $httpCode == 404 || $httpCode == 403) continue;
}

// 7. ส่งคำตอบ
if ($successResponse) {
    echo json_encode(['status' => 'success', 'answer' => $successResponse]);
} else {
    $msg = implode(", ", array_slice($debugErrors, 0, 3));
    echo json_encode(['status' => 'success', 'answer' => "⚠️ ระบบกำลังประมวลผล (กรุณารอสักครู่)"]); 
}
?>