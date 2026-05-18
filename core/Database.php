<?php

class Database {

    private static ?PDO $pdo = null;

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                ($_SERVER['HTTP_HOST']=="api.institutblaina.cm") ? "mysql:host=localhost;dbname=learn4kids;charset=utf8" : "mysql:host=db5019935106.hosting-data.io;dbname=dbs15392165;charset=utf8",
                ($_SERVER['HTTP_HOST']=="api.institutblaina.cm") ? "root" : "dbu217123",
                ($_SERVER['HTTP_HOST']=="api.institutblaina.cm") ? "" : "L3@rn4k!d52026!",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }

        return self::$pdo;
    }
}