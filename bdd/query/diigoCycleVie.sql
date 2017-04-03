SELECT 
    COUNT(DISTINCT d.doc_id) nb,
    SUBSTRING(t.code, 10, 3) codeStatut, t.code,
    DATE_FORMAT(d.pubDate, '%Y') tempsD
FROM
    flux_rapport r
        INNER JOIN
    flux_tag t ON t.tag_id = r.dst_id
        INNER JOIN
    flux_doc d ON d.doc_id = r.src_id
WHERE
    r.src_obj = 'doc' AND r.dst_obj = 'tag'
        AND r.pre_obj = 'acti'
        AND r.pre_id = 7
GROUP BY tempsD , codeStatut
ORDER BY tempsD , codeStatut