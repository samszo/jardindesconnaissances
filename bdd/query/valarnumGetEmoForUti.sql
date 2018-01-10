SELECT 
    SUM(r.niveau) value,
    GROUP_CONCAT(t.tag_id) tags,
    u.uti_id 'key',
    u.login 'type',
    GROUP_CONCAT(d.doc_id) docs,
    DATE_FORMAT(r.maj, '%Y-%m-%d %H:%i') temps,
    MIN(UNIX_TIMESTAMP(r.maj)) MinDate,
    MAX(UNIX_TIMESTAMP(r.maj)) MaxDate
FROM
    flux_rapport r
        INNER JOIN
    flux_doc d ON d.doc_id = r.src_id
        INNER JOIN
    flux_uti u ON u.uti_id = r.pre_id
        INNER JOIN
    flux_tag t ON t.tag_id = r.dst_id
WHERE
    r.monade_id = 5
GROUP BY u.uti_id , temps
ORDER BY temps