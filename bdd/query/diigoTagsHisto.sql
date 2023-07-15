SELECT 
    t.tag_id, t.code tag
    ,COUNT(DISTINCT rd.src_id) nbDoc
    , DATE_FORMAT(d.pubDate, "%Y-%m-%d") temps
    , MIN(d.pubDate) MinDate
    , MAX(d.pubDate) MaxDate
    , MIN(UNIX_TIMESTAMP(d.pubDate)) MinTS
    , MAX(UNIX_TIMESTAMP(d.pubDate)) MaxTS
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
-- WHERE
--   t.tag_id = 27 -- ecosystem info
GROUP BY t.tag_id, temps
ORDER BY t.code, temps