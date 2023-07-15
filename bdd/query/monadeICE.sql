SELECT r.niveau,
         count(DISTINCT utd.doc_id) nbDoc,
         count(DISTINCT utd.uti_id) nbUti,
		 count(DISTINCT utd.tag_id) nbTag,
         SUM(utd.poids) poids,
         COUNT(DISTINCT utd.utitagdoc_id) nbLien
    FROM flux_rapport r
         INNER JOIN flux_utitagdoc utd ON utd.utitagdoc_id = r.utitagdoc_id
WHERE r.monade_id = 2
GROUP BY r.niveau
