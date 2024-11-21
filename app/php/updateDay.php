<?php
if ($_POST['action'] === 'previous') {
    $date = new DateTime($_POST['date']);
    $date->modify('-1 day');

    echo urlencode($date->format('Y-m-d'));
} elseif ($_POST['action'] === 'next') {
    $date = new DateTime($_POST['date']);
    $date->modify('+1 day');

    echo urlencode($date->format('Y-m-d'));
}