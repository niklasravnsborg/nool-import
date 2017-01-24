<?php

require 'vendor/autoload.php';
use PHPHtmlParser\Dom;

header('Access-Control-Allow-Origin: *');

if (!isset($_FILES['file'])) {
	echo 'No file!';
	return;
}

$file_content = file_get_contents($_FILES['file']['tmp_name']);

$html = iconv('Windows-1252', 'UTF-8', $file_content);
$messages = [];

$dom = new Dom;
$dom->setOptions(['enforceEncoding' => 'UTF-8']);
$dom->load($html);

$date_raw = $dom->find('.mon_title')[0]->text(); // Date
$date = date('c', strtotime(split(' ', $date_raw)[0]));

$message_table = $dom->find('.mon_list')[0];
$message_rows  = $message_table->find('tr');

foreach ($message_rows as $message_row) {

	// catch error on no match
	if (!isset($message_row->find('td')[0])) {
		continue;
	}

	$columns = $message_row->find('td');

	// $replacement = $columns[3]->text;
	// $room        = $columns[4]->text;
	// $info        = $columns[6]->text;

	if ($columns[5]->text == 'Entfall') {
		$course  = strtolower($columns[0]->text);
		$lesson  = (int) $columns[1]->text;
		$teacher = $columns[2]->text;
		$type    = 'canceled';
	} else {
		continue;
	}

	$messages[] = [
		'course'  => $course,
		'lesson'  => $lesson,
		'date'    => $date,
		'teacher' => $teacher,
		'type'    => $type
	];
}

echo json_encode($messages);
