SELECT 
    *
FROM
    (SELECT 
        COUNT(d.doc_id) nbDoc,
            SUM(LENGTH(d.data)) nbOct,
            DATE_FORMAT(d.maj, '%Y-%m-%d %H:%i') tempsD
    FROM
        flux_doc d
    GROUP BY tempsD
    ORDER BY nbDoc DESC) doc,
    (SELECT 
        COUNT(r.rapport_id) nbTag,
            DATE_FORMAT(r.maj, '%Y-%m-%d %H:%i') tempsT
    FROM
        flux_rapport r
    WHERE
        r.src_obj = 'rapport'
            AND r.dst_obj = 'tag'
            AND r.pre_obj = 'acti'
    GROUP BY tempsT
    ORDER BY nbTag DESC) tag
WHERE
    doc.tempsD = tag.TempsT
ORDER BY doc.tempsD