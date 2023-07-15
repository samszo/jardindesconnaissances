SELECT COUNT(DISTINCT d.doc_id) nb, t.code, GROUP_CONCAT(DISTINCT(td.code)), GROUP_CONCAT(DISTINCT(u.login))
FROM flux_utitagdoc AS utd 
 INNER JOIN flux_doc AS d ON d.doc_id=utd.doc_id
 INNER JOIN flux_uti AS u ON u.uti_id = utd.uti_id
 INNER JOIN flux_tag AS t ON t.tag_id = utd.tag_id
 INNER JOIN flux_tagtag tt ON tt.tag_id_src = t.tag_id
 INNER JOIN flux_tag AS td ON td.tag_id = tt.tag_id_dst
WHERE (u.uti_id = 638) 
GROUP BY t.code
ORDER BY nb DESC
