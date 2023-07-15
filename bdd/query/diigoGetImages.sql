SELECT 
    d.titre,
    d.url,
    di.doc_id,
    di.url urlI,
    di.tronc,
    SUBSTRING(di.data, 1) ref,
    GROUP_CONCAT(DISTINCT t.code SEPARATOR '#' ) tags
FROM
    flux_doc d
        INNER JOIN
    flux_doc di ON di.parent = d.doc_id
        INNER JOIN
    flux_rapport r ON r.src_id = d.doc_id
        AND r.src_obj = 'doc'
        INNER JOIN
    flux_rapport rt ON rt.src_id = r.rapport_id
        AND rt.src_obj = 'rapport'
        AND rt.dst_obj = 'tag'
        INNER JOIN
    flux_tag t ON t.tag_id = rt.dst_id
WHERE
    d.parent = 1
GROUP BY d.doc_id, di.doc_id