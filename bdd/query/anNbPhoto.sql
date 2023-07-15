SELECT 
    dp.titre,
    COUNT(de.doc_id) nbTof,
    DATE_FORMAT(dgprDeb.valeur, '%Y-%m-%d') temps,
    MIN(DATEDIFF(DATE_FORMAT(dgprDeb.valeur, '%Y-%m-%d'),
            FROM_UNIXTIME(0)) * 24 * 3600) MinDate,
    MAX(DATEDIFF(DATE_FORMAT(dgprDeb.valeur, '%Y-%m-%d'),
            FROM_UNIXTIME(0)) * 24 * 3600) MaxDate,
    MAX(DATEDIFF(DATE_FORMAT(dgprFin.valeur, '%Y-%m-%d'),
            FROM_UNIXTIME(0)) * 24 * 3600) MaxFinDate
FROM
    flux_doc dp
        INNER JOIN
    flux_doc de ON de.lft BETWEEN dp.lft AND dp.rgt
        AND SUBSTRING(de.url, - 4) = '.jpg'
        INNER JOIN
    flux_rapport dgprDeb ON dgprDeb.src_id = dp.doc_id
        AND dgprDeb.src_obj = 'doc'
        AND dgprDeb.dst_id = 4
        AND dgprDeb.dst_obj = 'tag'
        LEFT JOIN
    flux_rapport dgprFin ON dgprFin.src_id = dp.doc_id
        AND dgprFin.src_obj = 'doc'
        AND dgprFin.dst_id = 5
        AND dgprFin.dst_obj = 'tag'
GROUP BY dp.doc_id , temps