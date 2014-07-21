SELECT t.code,
         t.desc,
         DATE_FORMAT(d.maj, "%Y") year,
         count(*) nb,
         count(DISTINCT utd.uti_id) nbUti,
         count(DISTINCT utd.doc_id) nbDoc
    FROM flux_tag t
         INNER JOIN flux_utitagdoc utd ON utd.tag_id = t.tag_id
         INNER JOIN flux_doc d ON utd.doc_id = d.doc_id
   WHERE d.titre LIKE "%numerique%"
GROUP BY t.code, year
ORDER BY t.code, year
