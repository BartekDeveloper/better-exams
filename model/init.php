<?php
include_once "database.php";
include_once "tables.php";

GenerateSQL_Code();

$initDb = new Database();

Database::Execute(GenerateSQL_Code());