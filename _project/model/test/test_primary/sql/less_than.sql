##
SELECT
    *
FROM
    `test_primary`
##filled{$id}##
WHERE
    `id_test_primary` < {$id}
##--