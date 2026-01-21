<?php
require_once '../sm-db.php';

// $q = mysql_query("SELECT id, 
//                          emailaddress,
//                          country,
//                          brand, COUNT(*) FROM users GROUP BY emailaddress,brand ORDER BY COUNT(*) DESC;");



$delete = mysql_query("DELETE users
                                    FROM users
                                    LEFT JOIN (
                                        SELECT id
                                        FROM users
                                        GROUP BY emailaddress, brand
                                    ) AS usersJoin ON users.id = usersJoin.id
                                    WHERE usersJoin.id IS NULL;");


if ($delete) {
    echo "Deleted Duplicates Record";
}
