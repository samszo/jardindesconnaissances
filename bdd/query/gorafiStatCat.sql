SELECT 
tc.code,
    COUNT(distinct d.parent) nbPage, 
    COUNT(distinct d.doc_id) nbArt
FROM
    flux_doc d
        INNER JOIN
    flux_rapport r ON r.src_id = d.doc_id
        AND r.src_obj = 'doc' AND r.dst_obj = 'tag' AND r.pre_obj = 'tag'
        INNER JOIN flux_tag tc on tc.tag_id = r.pre_id
-- WHERE t.code = 'italie'
GROUP BY tc.tag_id