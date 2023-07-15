SELECT t.tag_id, t.code, td.tag_id, td.code, count(distinct utd.doc_id) nbDoc
FROM flux_tagtag tt 
 INNER JOIN flux_tag AS t ON t.tag_id = tt.tag_id_src and tt.tag_id_src 
 INNER JOIN flux_tag AS td ON td.tag_id = tt.tag_id_dst and tt.tag_id_dst != 852
 INNER JOIN flux_utitagdoc utd ON utd.tag_id = tt.tag_id_dst AND utd.uti_id = 638
GROUP BY tt.tag_id_dst
order by td.code
