<?php
/**
 * @file DatajarSQL.php
 *
 * @brief Collection of utilities to deal with SQL back-ends.
 *
 * Copyright © 2012 Guillaume Pasquet
 *
 * This file is part of Datajar.
 *
 * Datajar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Datajar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Datajar.  If not, see <http://www.gnu.org/licenses/>.
 */
class DatajarSQL
{
    /**
     * Generates a WHERE statement out of an array.
     * @param where is the WHERE expression as array.
     * @param col_quote is the type of quotes used to delimit
     *   columns.
     * @param col_quote is the type of quotes used to delimit
     *   values.
     * @param escape is a function used to escape value strings.
     * @return the WHERE clause as a string.
     */
    public static function gen_where(array $where,
				     $col_quote = '',
				     $val_quote = "'",
				     $escape = null)
    {
        $where_stmt = '';

        $orig = $where_stmt;

        foreach($where as $col => $cond) {
            if($where_stmt != $orig) {
                if($col[0] == '|') { // OR
                    $where_stmt .= ' OR ';
                    if(strlen($col) > 1)
                        $col = substr($col, 1);
                } else {
                    $where_stmt .= ' AND ';
                }
            }

            if((is_numeric($col) && is_array($cond))
               ||
               ($col == '|' && is_array($cond))) {
                $where_stmt .= '(' . self::gen_where($cond, $col_quote, $val_quote, $escape) . ')';
            } else {
                $autocomp = false;
                $comp = '';
                if(preg_match('#[><=]$#', $col)) {
                    if(preg_match('#[><=]{2}$#', $col)) {
                        $comp = ' ' . substr($col, strlen($col) - 2) . ' ';
                        $col = trim(substr($col, 0, -2));
                    } else {
                        $comp = ' ' . substr($col, strlen($col) - 1) . ' ';
                        $col = trim(substr($col, 0, -1));
                    }
                }
                else if(preg_match('#!$#', $col)) {
                    $comp = ' != ';
                    $col = substr($col, 0, strlen($col) -1);
                }
                else if(preg_match('#%$#', $col)) {
                    $comp = ' LIKE ';
                    $col = substr($col, 0, strlen($col) -1);
                } else {
                    $autocomp = true;
                    $comp = " = ";
                }

                if(is_array($cond)) {
                    $cond_string = "";
                    foreach($cond as $c) {
                        $conj = ' AND ';
                        $quote = "'";
                        $operator = " = ";
                        if($c[0] == "|") {
                            $conj = ' OR ';
                            $c = substr($c, 1);
                        }
                        if($c[0] == "@") {
                            $c = substr($c, 1);
                            $operator = " ";
                        }
                        else if($c[0] == "#") {
                            $c = substr($c, 1);
                        }
                        else {
                            if($escape != null) {
                                $c = $val_quote . call_user_func($escape, $c) . $val_quote;
                            } else {
                                $c = $val_quote . $c . $val_quote;
                            }
                        }
                        if($cond_string == '') $conj = '';
                        $cond_string .= $conj . $col_quote . $col . $col_quote . $comp . $c;
                    }
                    $where_stmt .= $cond_string;
                } else {
                    if($cond[0] == "@") { // Verbatim (no quotes nor protection.)
                        $cond = substr($cond, 1);
                        if($autocomp)
                            $comp = " ";
                    }
                    else if($cond[0] == "#") {
                        $cond = substr($cond, 1);
                    }
                    else {
                        if($escape != null) {
                            $cond = $val_quote . call_user_func($escape, $cond) . $val_quote;
                        } else {
                            $cond = $val_quote . $cond . $val_quote;
                        }
                    }

                    $where_stmt .= $col_quote . $col . $col_quote . $comp . $cond;
                }
            }
        }

        return $where_stmt;
    }
}

?>
