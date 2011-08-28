SELECT uu.uti_id_src, ud.doc_id, COUNT(DISTINCT uu.uti_id_dst) nb, ud.maj
FROM flux_utiuti AS uu
INNER JOIN flux_utidoc ud on ud.uti_id = uu.uti_id_dst
WHERE (uu.uti_id_src = '1') 
GROUP BY uu.uti_id_src, ud.doc_id
ORDER BY ud.doc_id
