SELECT 
    d.titre,
    d.niveau,
    DATE_FORMAT(rDeb.valeur, '%Y-%m-%d') temps,
    MIN(DATEDIFF(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d'),
            FROM_UNIXTIME(0)) * 24 * 3600) MinDate,
    COUNT(de.doc_id) nbTof,
    GROUP_CONCAT(DISTINCT g.adresse) geos,
    GROUP_CONCAT(DISTINCT CONCAT(e.prenom,' ',e.nom)) exis
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
        LEFT JOIN
    flux_rapport rGeo ON rGeo.src_id = d.doc_id
        AND rGeo.src_obj = 'doc'
        AND rGeo.dst_obj = 'geo'
        LEFT JOIN
	flux_geo g ON g.geo_id = rGeo.dst_id
        LEFT JOIN
    flux_rapport rExi ON rExi.src_id = d.doc_id
        AND rExi.src_obj = 'doc'
        AND rExi.dst_obj = 'exi'
        LEFT JOIN
	flux_exi e ON e.exi_id = rExi.dst_id
        
	
GROUP BY d.doc_id , temps
ORDER BY niveau DESC
