SELECT 
    d.doc_id,
    d.url,
    d.titre,
    d.tronc,
    de.doc_id,
    de.url,
    de.titre,
    de.tronc,
    r.rapport_id, r.valeur,
    tp.tag_id tpId,
    tp.code tpCode,
    te.tag_id teId,
    te.code teCode
FROM
    flux_doc d
        INNER JOIN
    flux_doc de ON de.parent = d.doc_id
    
        INNER JOIN
    flux_rapport r ON r.pre_id = de.doc_id
        AND r.pre_obj = 'doc'
        AND r.src_obj = 'tag'
        AND r.dst_obj = 'tag'
        INNER JOIN
    flux_tag tp ON tp.tag_id = r.src_id
        INNER JOIN
    flux_tag te ON te.tag_id = r.dst_id
WHERE
    d.doc_id = 5 -- and tp.code like '%vie int%'
ORDER BY de.titre, tpCode , teCode, r.valeur