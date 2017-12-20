SELECT 
    r.rapport_id,
    r.maj,
    r.niveau,
    r.valeur,
    u.uti_id,
    u.login,
    t.tag_id,
    t.code,
    d.doc_id,
    d.url,
    d.titre,
    d.note,
    d.tronc,
    dp.doc_id,
    dp.url original,
    dgp.doc_id,
    dgp.titre label,
    dgp.note dgpNote
FROM
    flux_rapport r
        INNER JOIN
    flux_doc d ON d.doc_id = r.src_id
        INNER JOIN
    flux_doc dp ON dp.doc_id = d.parent
        INNER JOIN
    flux_doc dgp ON dgp.doc_id = dp.parent
        INNER JOIN
    flux_uti u ON u.uti_id = r.pre_id
        INNER JOIN
    flux_tag t ON t.tag_id = r.dst_id
WHERE
    r.monade_id = 3 AND dgp.note != ""
ORDER BY d.tronc
