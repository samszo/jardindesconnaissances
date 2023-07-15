
SELECT titre, nbTag*nbUti*nbLien ME
, FORMAT(1/(nbTag*nbUti*nbLien),10) ICE
, nbTag, nbUti
, poids
, nbLien
FROM (
SELECT d.titre,
         count(DISTINCT utd.tag_id) nbTag,
         count(DISTINCT utd.uti_id) nbUti,
         SUM(utd.poids) poids,
         COUNT(DISTINCT utd.utitagdoc_id) nbLien
    FROM flux_doc d
         INNER JOIN flux_utitagdoc utd ON utd.doc_id = d.doc_id
GROUP BY d.doc_id
 ) c
 ORDER BY ME DESC
