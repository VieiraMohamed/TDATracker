<?php
    session_start();
    session_destroy();
    //redirigir al index.html
    header("location: ../../index.html");
    exit;
?>