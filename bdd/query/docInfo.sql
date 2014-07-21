SELECT d.titre,
         t.tag_id, t.code,
         u.uti_id, u.login
    FROM flux_doc d
         INNER JOIN flux_utitagdoc utd ON utd.doc_id = d.doc_id
         INNER JOIN flux_tag t ON t.tag_id = utd.tag_id
         INNER JOIN flux_uti u ON u.uti_id = utd.uti_id
   WHERE d.doc_id = 11167 -- IN (11167, 11168)
-- GROUP BY d.doc_id
-- GROUP BY t.tag_id
