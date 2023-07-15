
SELECT code, nbDoc*nbUti*nbLien ME
, FORMAT(1/(nbDoc*nbUti*nbLien),10) ICE
, nbDoc, nbUti
, poids
, nbLien
FROM (
SELECT t.code,
         count(DISTINCT utd.doc_id) nbDoc,
         count(DISTINCT utd.uti_id) nbUti,
         SUM(utd.poids) poids,
         COUNT(DISTINCT utd.utitagdoc_id) nbLien
    FROM flux_tag t
         INNER JOIN flux_utitagdoc utd ON utd.tag_id = t.tag_id
GROUP BY t.tag_id
 ) c
 ORDER BY ME DESC
