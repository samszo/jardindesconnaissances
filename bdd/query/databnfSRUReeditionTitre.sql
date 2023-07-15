select 
    count(d.doc_id) nbDoc
    , group_concat(rProp1.valeur) Years
    , rProp2.valeur title

from flux_doc d
inner join flux_rapport rProp1 on  rProp1.src_id = d.doc_id AND rProp1.src_obj = 'doc' AND rProp1.dst_obj = 'tag' AND rProp1.dst_id = 4
inner join flux_rapport rProp2 on  rProp2.src_id = d.doc_id AND rProp2.src_obj = 'doc' AND rProp2.dst_obj = 'tag' AND rProp2.dst_id = 3
-- where d.doc_id = 18849
group by rProp2.valeur 
order by nbDoc desc