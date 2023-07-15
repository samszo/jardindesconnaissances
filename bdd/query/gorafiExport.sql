SELECT 
    tc.code categorie,
    d.doc_id,
    d.titre,
    d.url,
    di.url image,
    r.niveau commentaire
FROM
    flux_doc d
        INNER JOIN
    flux_rapport r ON r.src_id = d.doc_id
        AND r.src_obj = 'doc'
        AND r.dst_obj = 'tag'
        AND r.pre_obj = 'tag'
        INNER JOIN
    flux_tag tc ON tc.tag_id = r.pre_id
        INNER JOIN
    flux_doc di ON di.parent = d.doc_id
ORDER BY commentaire DESC