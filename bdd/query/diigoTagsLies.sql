SELECT 
    t.tag_id,
    t.code,
    tl.tag_id,
    tl.code,
    COUNT(DISTINCT rl.src_id) nbDocL
FROM
    flux_tag t
        INNER JOIN
    flux_rapport r ON t.tag_id = r.dst_id
        AND r.pre_obj = 'acti'
        AND r.pre_id = 2
        INNER JOIN
    flux_rapport rl ON rl.src_id = r.src_id
        AND rl.src_obj = 'rapport'
        INNER JOIN
    flux_tag tl ON tl.tag_id = rl.dst_id
WHERE
    t.tag_id = 27
GROUP BY tl.tag_id
ORDER BY nbDocL DESC