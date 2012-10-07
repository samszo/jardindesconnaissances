-- /*
select 
-- * 
  count(distinct doc_id) nbDoc 
-- , count(distinct tag_id) nbTag
 , count(distinct tag_id_dst) nbType
 , tag_id, code
 , zemanta, yahoo, alchemy
-- , group_concat(distinct type) type
FROM (
-- */
  select 
    t.tag_id, t.code
    ,td.doc_id
    ,tt.tag_id_dst
    ,ut1.uti_id zemanta
    ,ut2.uti_id yahoo
    ,ut3.uti_id alchemy
    , d.branche_lft, d.tronc 
    , tTy.code type
  from flux_tag t
  inner join flux_utitagdoc td on td.tag_id = t.tag_id
  inner join flux_doc d on d.doc_id = td.doc_id and d.type = 57
  
  left join flux_tagtag tt on tt.tag_id_src = t.tag_id
  left join flux_tag tTy on tTy.tag_id = tt.tag_id_dst
--  inner join flux_utitag utTy on utTy.tag_id = tTy.tag_id AND utTy.uti_id = 5
  
  left join flux_utitagdoc ut1 on ut1.tag_id = t.tag_id AND ut1.doc_id = d.doc_id AND ut1.uti_id = 8
--  left join flux_utidoc ud1 on ud1.doc_id = d.doc_id  AND ud1.uti_id = ut1.uti_id 

  left join flux_utitagdoc ut2 on ut2.tag_id = t.tag_id AND ut2.doc_id = d.doc_id AND ut2.uti_id = 5
--  left join flux_utidoc ud2 on ud2.doc_id = d.doc_id  AND ud2.uti_id = ut2.uti_id 
  
  left join flux_utitagdoc ut3 on ut3.tag_id = t.tag_id AND ut3.doc_id = d.doc_id AND ut3.uti_id = 7
--  left join flux_utidoc ud3 on ud3.doc_id = d.doc_id  AND ud3.uti_id = ut3.uti_id 

-- WHERE ut1.uti_id = 8

-- group by td.doc_id
-- order by d.branche_lft

-- /*
) concat
 WHERE 
zemanta = 8  AND 
yahoo = 5 AND
alchemy = 7 
 
 group by tag_id
-- order by tag_id
-- */