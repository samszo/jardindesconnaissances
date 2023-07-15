SELECT 
    d.doc_id,
    d.titre,
    dp.doc_id idParent,
    dp.titre titreParent,
    MIN(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d')) temps,
    MIN(DATEDIFF(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d'),
            FROM_UNIXTIME(0)) * 24 * 3600) MinDate,
    GROUP_CONCAT(DISTINCT rGoS.valeur) score,
    GROUP_CONCAT(DISTINCT rGoS.niveau) magnitude,
    COUNT(DISTINCT dv.doc_id) nbVisage,
--    COUNT(DISTINCT de.doc_id) nbPhoto,
    COUNT(DISTINCT t.tag_id) nbTag,
    GROUP_CONCAT(DISTINCT t.tag_id) tags,
    COUNT(DISTINCT e.exi_id) nbExi,
    GROUP_CONCAT(DISTINCT e.exi_id) exis,
    COUNT(DISTINCT g.geo_id) nbGeo,
    GROUP_CONCAT(DISTINCT g.geo_id) geos
FROM
    flux_doc d
--        INNER JOIN
--    flux_doc de ON de.parent = d.doc_id AND SUBSTRING(de.url, - 4) = '.jpg'
        INNER JOIN
    flux_doc dp ON dp.doc_id = d.parent
        INNER JOIN
    flux_rapport rDeb ON rDeb.src_id = dp.doc_id
        AND rDeb.src_obj = 'doc'
        AND rDeb.dst_obj = 'tag'
        AND rDeb.dst_id = 4
        LEFT JOIN
    flux_doc dGo ON dGo.parent = d.doc_id
        AND dGo.titre = 'Flux_Glanguage_Flux_Glanguage::sauveAnalyseTexte'
        LEFT JOIN
    flux_rapport rGoS ON rGoS.src_id = dGo.doc_id
        AND rGoS.src_obj = 'doc'
        AND rGoS.dst_obj = 'tag'
        AND rGoS.dst_id = 7
        LEFT JOIN
    flux_rapport rGoT ON rGoT.src_id = dGo.doc_id
        AND rGoT.src_obj = 'doc'
        AND rGoT.dst_obj = 'tag'
        AND rGoT.dst_id <> 7
        LEFT JOIN
    flux_tag t ON t.tag_id = rGoT.dst_id
        LEFT JOIN
    flux_rapport rGoE ON rGoE.src_id = dGo.doc_id
        AND rGoE.src_obj = 'doc'
        AND rGoE.dst_obj = 'exi'
        LEFT JOIN
    flux_exi e ON e.exi_id = rGoE.dst_id
        LEFT JOIN
    flux_rapport rGoG ON rGoG.src_id = dGo.doc_id
        AND rGoG.src_obj = 'doc'
        AND rGoG.dst_obj = 'geo'
        LEFT JOIN
    flux_geo g ON g.geo_id = rGoG.dst_id
        LEFT JOIN
    flux_doc dv ON dv.parent = d.doc_id
    
            LEFT JOIN
    flux_rapport rGeo ON rGeo.src_id = d.doc_id
        AND rGeo.src_obj = 'doc'
        AND rGeo.dst_obj = 'geo'
        LEFT JOIN
	flux_geo gAN ON g.geo_id = rGeo.dst_id
        LEFT JOIN
    flux_rapport rExi ON rExi.src_id = d.doc_id
        AND rExi.src_obj = 'doc'
        AND rExi.dst_obj = 'exi'
        LEFT JOIN
	flux_exi eAN ON e.exi_id = rExi.dst_id

WHERE
    d.doc_id = 5042
