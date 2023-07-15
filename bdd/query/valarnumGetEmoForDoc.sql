SELECT 
    SUM(r.niveau) value,
    GROUP_CONCAT(t.tag_id) tags,
    CONCAT(dgp.doc_id,'_',dp.doc_id) 'key',
    CONCAT(dgp.titre, ' : ',dp.titre) 'type',
    dp.note,
    GROUP_CONCAT(d.note) evals,
    GROUP_CONCAT(u.uti_id) utis,
    DATE_FORMAT(r.maj, '%Y-%m-%d %H:%i') temps,
    MIN(UNIX_TIMESTAMP(r.maj)) MinDate,
    MAX(UNIX_TIMESTAMP(r.maj)) MaxDate
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
    r.monade_id = 3
GROUP BY dp.doc_id, temps
ORDER BY temps