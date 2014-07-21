SELECT d.doc_id, d.titre,
         count(DISTINCT de.doc_id) nbDocEnf,
         count(DISTINCT utd.uti_id) nbUtiEnf,
         count(DISTINCT utd.tag_id) nbTagEnf,
		 1/(count(DISTINCT de.doc_id)*count(DISTINCT utd.uti_id)*count(DISTINCT utd.tag_id)*4) iceEnf
    FROM flux_doc d
         INNER JOIN flux_doc de ON de.tronc = d.doc_id
         INNER JOIN flux_utitagdoc utd ON utd.doc_id = de.doc_id
GROUP BY d.doc_id
-- GROUP BY t.tag_id
ORDER BY iceEnf
