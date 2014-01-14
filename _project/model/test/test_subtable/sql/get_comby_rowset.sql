##
SELECT
    TS.*,
    TP.`date`        AS __date,
    TP.`is_complete` AS __is_complete
FROM
    `test_subtable` TS
INNER JOIN `test_primary` TP
    USING (`id_test_primary`)

WHERE
    1 = 1

##filled{$date}##
    AND TP.`date` IN ({$date})
##--

##exist{$complete}##
    AND TP.`is_complete` = {$complete}
##--

GROUP BY
    `id_test_primary`