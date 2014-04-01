SELECT t.code,
         t.desc,
         DATE_FORMAT(d.maj, "%Y") year,
         count(*) nb,
         count(DISTINCT utd.uti_id) nbUti,
         count(DISTINCT utd.doc_id) nbDoc, 
		 tGeo.code
    FROM flux_tag t
         INNER JOIN flux_utitagdoc utd ON utd.tag_id = t.tag_id
         INNER JOIN flux_doc d ON utd.doc_id = d.doc_id
         INNER JOIN flux_utitagdoc utdGeo ON utdGeo.doc_id = utd.doc_id
         INNER JOIN flux_tag tGeo ON tGeo.tag_id = utdGeo.tag_id AND tGeo.desc = "pays"
   WHERE t.code LIKE "%information%" OR t.code LIKE "%communication%"
GROUP BY t.code, year
ORDER BY t.code, year
