SELECT 
    t.code,
    e.exi_id,
    e.nom,
    d.doc_id,
    d.titre,
    d.url,
    r.valeur,
    ev.exi_id,
    ev.nom
    -- r.*,
    -- rv.*
FROM
    flux_rapport r
        INNER JOIN
    flux_tag t ON t.tag_id = r.dst_id AND t.parent = 16
        INNER JOIN
    flux_exi e ON e.exi_id = r.src_id
        INNER JOIN
    flux_doc d ON d.doc_id = r.pre_id
    inner join flux_rapport rv on rv.dst_id = r.rapport_id and rv.dst_obj='rapport' and rv.src_obj = 'exi' and rv.pre_obj = 'rapport'
    inner join flux_exi ev on ev.exi_id = rv.src_id 
 WHERE
    r.src_obj = 'exi' AND r.dst_obj = 'tag'
        AND r.pre_obj = 'doc'