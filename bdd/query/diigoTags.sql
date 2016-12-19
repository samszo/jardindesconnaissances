SELECT 
    COUNT(DISTINCT r.rapport_id) nbTag,
--    COUNT(DISTINCT r.src_id) nbDoc,
    COUNT(DISTINCT rl.dst_id) nbDoc,
    t.tag_id,
    t.code tag,
    a.code acti
FROM
    flux_tag t
        INNER JOIN
    flux_rapport r ON 
        r.pre_obj = 'acti'
        AND r.pre_id = 2
        AND t.tag_id = r.dst_id
        INNER JOIN
    flux_acti a ON a.acti_id = r.pre_id
        INNER JOIN
    flux_rapport rl ON rl.src_id = r.src_id
        AND rl.src_obj = 'rapport'
        INNER JOIN
    flux_tag tl ON tl.tag_id = rl.dst_id
 WHERE
    t.tag_id = 27 -- ecosystem info
GROUP BY t.tag_id
ORDER BY nbTag DESC