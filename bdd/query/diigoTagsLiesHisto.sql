SELECT 
    t.tag_id,
    t.code tag,
    DATE_FORMAT(d.pubDate, '%Y-%m-%d') temps,
--    MIN(UNIX_TIMESTAMP(d.pubDate)) MinDate,
--    MAX(UNIX_TIMESTAMP(d.pubDate)) MaxDate,
     COUNT(DISTINCT rd.src_id) nbDoc,
--    tl.tag_id idTagL,
--    tl.code tagL
    group_concat(tl.tag_id) idTagL,
    group_concat(tl.code) tagL
FROM
    flux_tag t
        INNER JOIN
    flux_rapport r ON r.monade_id = 2
        AND r.src_obj = 'rapport'
        AND r.dst_obj = 'tag'
        AND r.pre_obj = 'acti'
        AND r.pre_id = 2
        AND t.tag_id = r.dst_id
        INNER JOIN
    flux_rapport rd ON rd.rapport_id = r.src_id
        INNER JOIN
    flux_doc d ON d.doc_id = rd.src_id AND d.parent = 1
        INNER JOIN
    flux_rapport rl ON rl.src_id = r.src_id
        AND rl.src_obj = 'rapport'
        INNER JOIN
    flux_tag tl ON tl.tag_id = rl.dst_id
WHERE
    t.tag_id = 27
-- GROUP BY idTagL , temps
 GROUP BY temps
-- HAVING nbDoc > 3
-- ORDER BY t.code , temps;
ORDER BY temps;

