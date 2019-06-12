<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Mongo Aggregate Expression Facading.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 *               https://docs.mongodb.com/manual/meta/aggregation-quick-reference/#aggregation-expressions
 * $abs $add $addToSet $allElementsTrue $and $anyElementTrue $arrayElemAt $arrayToObject $avg $cmp $concat $concatArrays $cond
 * $dateFromParts $dateToParts $dateFromString $dateToString $dayOfMonth $dayOfWeek $dayOfYear $divide $eq $exp $filter $first
 * $floor $gt $gte $hour $ifNull $in $indexOfArray $indexOfBytes $indexOfCP $isArray $isoDayOfWeek $isoWeek $isoWeekYear $last
 * $let $literal $ln $log $log10 $lt $lte $ltrim $map $max $mergeObjects $meta $min $millisecond $minute $mod $month $multiply
 * $ne $not $objectToArray $or $pow $push $range $reduce $reverseArray $rtrim $second $setDifference $setEquals $setIntersection
 * $setIsSubset $setUnion $size $slice $split $sqrt $stdDevPop $stdDevSamp $strcasecmp $strLenBytes $strLenCP $substr $substrBytes
 * $substrCP $subtract $sum $switch $toLower $toUpper $trim $trunc $type $week $year $zip
 */
class Expr
{
    /**
     * @param $expression
     * @return Expression
     */
    public static function multiply($expression): Expression
    {
        return new Expression('multiply', $expression);
    }

    /**
     * @param $expression
     * @return Expression
     */
    public static function sum($expression): Expression
    {
        return new Expression('sum', $expression);
    }

    /**
     * @param $expression
     * @return Expression
     */
    public static function year($expression): Expression
    {
        return new Expression('year', $expression);
    }

    /**
     * @param $expression
     * @return Expression
     */
    public static function month($expression): Expression
    {
        return new Expression('month', $expression);
    }

    /**
     * @param $expression
     * @return Expression
     */
    public static function day($expression): Expression
    {
        return new Expression('dayOfMonth', $expression);
    }

    /**
     * @param $expression
     * @return Expression
     */
    public static function last($expression): Expression
    {
        return new Expression('last', $expression);
    }

    /**
     * @param $expression
     * @return Expression
     */
    public static function first($expression): Expression
    {
        return new Expression('first', $expression);
    }

    /**
     * @param $expression
     * @return Expression
     */
    public static function avg($expression): Expression
    {
        return new Expression('avg', $expression);
    }

    /**
     * @param $expression
     * @return Expression
     */
    public static function push($expression): Expression
    {
        return new Expression('push', $expression);
    }

    /**
     * @param $name
     * @param $expression
     * @return Expression
     */
    public static function __callStatic( $name, $expression ): Expression
    {
        return new Expression($name, $expression);
    }
}