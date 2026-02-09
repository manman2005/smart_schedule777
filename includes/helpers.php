<?php
// smart_schedule/includes/helpers.php
// ฟังก์ชันตัดคำนำหน้าชื่อ (นาย, นางสาว, นาง, ดร., ผศ., ฯลฯ) ออก
function stripThaiPrefix($name) {
    if (empty($name) || !is_string($name)) return $name;
    return preg_replace('/^(นาย|นางสาว|นาง|ว่าที่ร้อยตรี|ว่าที่ร\.ต\.|ดร\.|ผศ\.|รศ\.|ศ\.|อ\.|อาจารย์)\s*/u', '', trim($name));
}