SELECT titre, nbTag*nbUti ME, nbDocEnf*nbTagEnf*nbUtiEnf MEEnf
, 1/(3*nbTag*nbUti*4*nbDocEnf*nbTagEnf*nbUtiEnf) ICE
, 1/(4*nbDocEnf*nbTagEnf*nbUtiEnf) ICEEnf
FROM (
SELECT d.titre,
         count(DISTINCT utd.tag_id) nbTag,
         count(DISTINCT utd.uti_id) nbUti
		 , count(DISTINCT de.doc_id) nbDocEnf,
         count(DISTINCT utdE.tag_id) nbTagEnf,
         count(DISTINCT utdE.uti_id) nbUtiEnf
    FROM flux_doc d
         INNER JOIN flux_utitagdoc utd ON utd.doc_id = d.doc_id
         INNER JOIN flux_doc de ON de.tronc = d.doc_id
         INNER JOIN flux_utitagdoc utdE ON utdE.doc_id = de.doc_id
GROUP BY d.doc_id) c
-- GROUP BY t.tag_id
-- ORDER BY nbDocEnf DESC
ORDER BY ME DESC
