SELECT 
    t.tag_id, t.code tag
    ,COUNT(DISTINCT rd.src_id) nbDoc
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
-- WHERE
--    t.tag_id = 14 -- ecosystem info
GROUP BY t.tag_id