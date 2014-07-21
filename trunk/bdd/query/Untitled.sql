SELECT doc_id, titre, nbTag*nbUti ME, nbDocEnf*nbTagEnf*nbUtiEnf MEEnf
, FORMAT(1/(2*nbTag*nbUti*nbDocEnf*nbTagEnf*nbUtiEnf), 6) ICE
, FORMAT(1/(nbDocEnf*nbTagEnf*nbUtiEnf), 6) ICEEnf
, nbDoc, nbTag, nbUti, nbDocEnf,nbTagEnf,nbUtiEnf
FROM (
SELECT d.doc_id, d.titre,
         count(DISTINCT utd.doc_id) nbDoc,
         count(DISTINCT utd.tag_id) nbTag,
         count(DISTINCT utd.uti_id) nbUti,
         count(DISTINCT utdE.doc_id) nbDocEnf,
         count(DISTINCT utdE.tag_id) nbTagEnf,
         count(DISTINCT utdE.uti_id) nbUtiEnf
    FROM flux_doc d
         INNER JOIN flux_utitagdoc utd ON utd.doc_id = d.doc_id AND utd.tag_id
         INNER JOIN flux_doc de ON de.tronc = d.doc_id AND d.score > 0
         INNER JOIN flux_utitagdoc utdE ON utdE.doc_id = de.doc_id
GROUP BY d.doc_id) c
-- GROUP BY t.tag_id
-- ORDER BY nbDocEnf DESC
-- ORDER BY ME DESC
-- ORDER BY ICEEnf DESC
 ORDER BY ICE -- DESC
-- ORDER BY nbTag -- DESC
-- ORDER BY nbUti DESC