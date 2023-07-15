SELECT uu.uti_id_dst, uu.network, uu.fan, uu.post, u.login, COUNT(*) nbDoc
FROM flux_utiuti AS uu
INNER JOIN flux_utidoc ud on ud.uti_id = uu.uti_id_dst
INNER JOIN flux_uti u on u.uti_id = uu.uti_id_dst
WHERE (uu.uti_id_src = '1') 
GROUP BY uu.uti_id_dst
ORDER BY nbDoc DESC