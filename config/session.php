<?php

// if session doesn´t exist we start new one
if(session_status() === PHP_SESSION_NONE){
    session_start();
}