SELECT 
    COUNT(r.rapport_id) nb, r.valeur, r.src_id,
    group_concat(r.rapport_id) ids
FROM
    flux_rapport r
WHERE
    r.dst_id = 11 AND r.dst_obj = 'tag' AND r.valeur != ""
GROUP BY r.valeur
HAVING nb > 1
ORDER BY nb DESC
