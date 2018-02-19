SELECT 
    COUNT(DISTINCT d.doc_id) nbD,
    SUBSTRING(dv.titre, 1, 10),
    COUNT(DISTINCT dv.doc_id) nbDv
--    d.titre, d.url
--    ,dv.titre
FROM
    flux_doc d
        INNER JOIN
    flux_doc dv ON dv.parent = d.doc_id
        AND (dv.titre LIKE 'analyzeEntities%'
        OR dv.titre LIKE 'analyzeClassifyText%'
        OR dv.titre LIKE 'analyzeEntitySentiment%'
        OR dv.titre LIKE 'analyzeSyntax%'
        OR dv.titre LIKE 'analyzeSentiment%')
GROUP BY SUBSTRING(dv.titre, 1, 10)
-- ORDER BY d.doc_id, dv.titre