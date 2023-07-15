SELECT 
    d.titre titreEnfant,
    GROUP_CONCAT(DISTINCT dp.titre) pathParent, 
    -- DATE_FORMAT(rDeb.valeur, '%Y-%m-%d') temps,
    MIN(DATE_FORMAT(rDeb.valeur, '%Y')) an
FROM
    flux_doc d
        INNER JOIN
    flux_doc dp ON d.lft between dp.lft and dp.rgt
        LEFT JOIN
    flux_rapport rDeb ON rDeb.src_id = dp.doc_id
        AND rDeb.src_obj = 'doc'
        AND rDeb.dst_obj = 'tag'
        AND rDeb.dst_id = 4
WHERE
    rDeb.valeur != ''
group by d.titre
ORDER BY an