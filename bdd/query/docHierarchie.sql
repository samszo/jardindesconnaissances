SELECT d.titre, count(DISTINCT de.doc_id) nbDocEnf, count(DISTINCT utd.tag_id) nbTagEnf
    FROM flux_doc d INNER JOIN flux_doc de ON de.tronc = d.doc_id
	inner join flux_utitagdoc utd on utd.doc_id = de.doc_id
GROUP BY d.doc_id
-- GROUP BY t.tag_id
ORDER BY nbDocEnf DESC