
SELECT code, nbDoc*nbUti ME
, 1/(nbDoc*nbUti) ICE
, nbDoc, nbUti
, utPoids, tdPoids
FROM (
SELECT t.code,
         count(DISTINCT td.doc_id) nbDoc,
         count(DISTINCT ut.uti_id) nbUti,
         SUM(ut.poids) utPoids,
         SUM(td.poids) tdPoids
    FROM flux_tag t
         INNER JOIN flux_utitag ut ON ut.tag_id = t.tag_id
         INNER JOIN flux_tagdoc td ON td.tag_id = t.tag_id
GROUP BY t.tag_id
 ) c
 ORDER BY ME DESC
