SELECT * FROM (
SELECT 
    COUNT(*) nb,
    GROUP_CONCAT(d.doc_id) ids,
    SUBSTRING(d.url,
        (LOCATE('_', d.url) + 1),
        LOCATE('_', d.url, LOCATE('_', d.url) + 1) - LOCATE('_', d.url) - 1) as prefixe,
    SUBSTRING(d.url,
        (LOCATE('_', d.url, LOCATE('_', d.url) + 1) + 1),
        LOCATE('_L', d.url)-(LOCATE('_', d.url, LOCATE('_', d.url) + 1) + 1)) as num,
    SUBSTRING(d.url,
        (LOCATE('_', d.url) + 1),
        LOCATE('_L', d.url) - LOCATE('_', d.url) - 1) court,
    d.url
FROM
    flux_doc d
WHERE
    d.type = 1
GROUP BY d.url
) idx
ORDER BY nb DESC, prefixe, num
