SELECT d.doc_id, GROUP_CONCAT(DISTINCT t.code ORDER BY t.code DESC SEPARATOR ' - '), GROUP_CONCAT(DISTINCT td.code ORDER BY t.code DESC SEPARATOR ' - '), u.login
FROM flux_utitagdoc AS utd 
 INNER JOIN flux_doc AS d ON d.doc_id=utd.doc_id
 INNER JOIN flux_uti AS u ON u.uti_id = utd.uti_id
 INNER JOIN flux_tag AS t ON t.tag_id = utd.tag_id and t.code != ""
 INNER JOIN flux_tagtag tt ON tt.tag_id_dst = t.tag_id
 INNER JOIN flux_tag AS td ON td.tag_id = tt.tag_id_src
WHERE (u.uti_id = 638) 
GROUP BY d.doc_id
