
SELECT login, uti_id, nbTag*nbUti*nbLien*nbDoc ME
, FORMAT(1/(nbTag*nbUti*nbLien*nbDoc),10) ICE
, nbTag, nbUti
, poids
, nbLien
, nbDoc
FROM (
SELECT u.login, u.uti_id,
         count(DISTINCT utd.doc_id) nbDoc,
         count(DISTINCT utd.tag_id) nbTag,
         count(DISTINCT utd.uti_id) nbUti,
         SUM(utd.poids) poids,
         COUNT(DISTINCT utd.utitagdoc_id) nbLien
    FROM flux_uti u
         INNER JOIN flux_utitagdoc utd ON utd.uti_id = u.uti_id
GROUP BY u.uti_id
 ) c
 ORDER BY ME DESC
