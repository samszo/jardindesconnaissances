SELECT u.login, COUNT(DISTINCT utd.doc_id) nbDoc, COUNT(DISTINCT utd.tag_id) nbTag  
FROM flux_utitagdoc utd
INNER JOIN flux_uti u ON u.uti_id = utd.uti_id
GROUP BY u.login