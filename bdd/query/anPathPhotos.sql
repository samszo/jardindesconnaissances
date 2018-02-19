SELECT 
    -- d.titre titreDoc,
    -- de.titre titreNiv,
    -- dpe.titre titrePhoto,
    concat(IFNULL(de.titre,' '), ' - ', IFNULL(dpe.titre,' ')) contenu
    -- MIN(DATE_FORMAT(rDeb.valeur, '%Y-%m-%d')) temps,
    -- MIN(DATE_FORMAT(rDeb.valeur, '%Y')) an
    
FROM
    flux_doc d
        INNER JOIN
    flux_doc de ON de.lft BETWEEN d.lft AND d.rgt
        LEFT JOIN
    flux_rapport rDeb ON rDeb.src_id = de.doc_id
        AND rDeb.src_obj = 'doc'
        AND rDeb.dst_obj = 'tag'
        AND rDeb.dst_id = 4
    
        LEFT JOIN
    flux_doc dpe ON dpe.parent = de.doc_id
        LEFT JOIN
    flux_doc dt ON dt.parent = de.doc_id AND dt.type = 1
WHERE
    d.niveau = 2 and de.type is null  AND rDeb.valeur != '' --  AND de.titre != ''
GROUP BY d.titre, de.titre, dpe.titre, dpe.doc_id
ORDER BY DATE_FORMAT(rDeb.valeur, '%Y')