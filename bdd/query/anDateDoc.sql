SELECT 
    d.titre,
    d.niveau,
    DATE_FORMAT(rDeb.valeur, '%Y-%m-%d') temps,
    MIN(DATEDIFF(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d'),
            FROM_UNIXTIME(0)) * 24 * 3600) MinDate,
    COUNT(de.doc_id) nbTof
FROM
    flux_doc d
        INNER JOIN
    flux_rapport rDeb ON rDeb.src_id = d.doc_id
        AND rDeb.src_obj = 'doc'
        AND rDeb.dst_obj = 'tag'
        AND rDeb.dst_id = 4
        INNER JOIN
    flux_doc de ON de.parent = d.doc_id
        AND SUBSTRING(de.url, - 4) = '.jpg'
	
GROUP BY d.doc_id , temps
ORDER BY niveau DESC
