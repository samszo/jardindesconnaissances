SELECT 
dp.titre
, count(de.doc_id) nbTof 
,	DATE_FORMAT(dgprDeb.valeur, '%Y-%m-%d') temps
,	MIN(DATEDIFF(DATE_FORMAT(dgprDeb.valeur, '%Y-%m-%d'),
            FROM_UNIXTIME(0)) * 24 * 3600) MinDate
,	MAX(DATEDIFF(DATE_FORMAT(dgprDeb.valeur, '%Y-%m-%d'),
            FROM_UNIXTIME(0)) * 24 * 3600) MaxDate
,	MAX(DATEDIFF(DATE_FORMAT(dgprFin.valeur, '%Y-%m-%d'),
            FROM_UNIXTIME(0)) * 24 * 3600) MaxFinDate


FROM flux_doc dp
inner join flux_doc de on de.lft BETWEEN dp.lft AND dp.rgt AND SUBSTRING(de.url, -4) = '.jpg'
inner join flux_rapport dgprDeb on dgprDeb.src_id = dp.doc_id AND dgprDeb.src_obj = 'doc' AND dgprDeb.dst_id = 4 AND dgprDeb.dst_obj = 'tag'
left join flux_rapport dgprFin on dgprFin.src_id = dp.doc_id AND dgprFin.src_obj = 'doc' AND dgprFin.dst_id = 5 AND dgprFin.dst_obj = 'tag'
-- WHERE dp.niveau = 3
group by dp.doc_id, temps