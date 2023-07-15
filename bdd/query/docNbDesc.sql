SELECT t.tag_id, t.code
, count(DISTINCT utd.doc_id) nbDoc
 , SUM(MATCH (titre, note) AGAINST ("sciences+ information+ communication+")) AS score
    FROM flux_tag t
         INNER JOIN flux_utitagdoc utd ON utd.tag_id = t.tag_id
         INNER JOIN flux_doc d ON d.doc_id = utd.doc_id 
   WHERE t.desc = "pays" 
   -- AND d.note LIKE "%sciences de l'information et de la communication%"
    AND MATCH (d.titre, d.note) AGAINST ("sciences+ information+ communication+")
GROUP BY t.tag_id
ORDER BY nbDoc DESC
