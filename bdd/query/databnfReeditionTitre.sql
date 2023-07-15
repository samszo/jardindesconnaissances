SELECT 
   -- dDat.titre, dDat.url,
   -- SUBSTRING(dDat.url, -11),
   -- r.rapport_id,
    group_concat(rProp1.valeur) Years
    , rProp2.valeur title
FROM
    flux_doc dDat
    inner join flux_rapport r on r.src_id = dDat.doc_id AND r.src_obj = 'doc' and r.dst_obj='acti' and r.dst_id = 4
    inner join flux_rapport rProp1 on rProp1.pre_id = r.rapport_id
    inner join flux_tag t1 on t1.tag_id = rProp1.dst_id and t1.tag_id = 62 
    inner join flux_rapport rProp2 on rProp2.pre_id = r.rapport_id
    inner join flux_tag t2 on t2.tag_id = rProp2.dst_id and t2.tag_id = 65  
where CONVERT(rProp1.valeur,UNSIGNED) BETWEEN 1800 and 1899 
group by title 
order by title; 