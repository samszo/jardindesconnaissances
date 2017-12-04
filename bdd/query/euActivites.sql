SELECT 
    t.code
    , r.rapport_id, r.valeur
    , rC.valeur
    , tC.tag_id, tC.code
    , dD.doc_id, dD.titre
FROM
    flux_tag t
    inner join flux_rapport r on r.src_id = t.tag_id and r.src_obj = 'tag' and (r.dst_obj = 'rapport' or r.dst_obj = 'doc')  and r.pre_obj = 'rapport' 
    -- commission
    left join flux_rapport rC on rC.rapport_id = r.dst_id
    left join flux_tag tC on tC.tag_id = rC.dst_id
    -- commission
    left join flux_doc dD on dD.doc_id = r.dst_id

WHERE
    t.parent = 13
Order by r.valeur, rC.valeur