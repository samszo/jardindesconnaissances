SELECT tag.*, COUNT(DISTINCT utd.doc_id) nb, GROUP_CONCAT(DISTINCT utd.doc_id) idsDoc
, GROUP_CONCAT(DISTINCT tagParent.tag_id order by tagParent.lft)
FROM flux_tag AS tag
INNER JOIN flux_utitagdoc utd ON utd.tag_id = tag.tag_id AND utd.uti_id = 638

INNER JOIN flux_tag AS tagParent ON tag.lft BETWEEN tagParent.lft AND tagParent.rgt

WHERE tag.desc != ''
GROUP BY tag.tag_id
ORDER BY tag.lft;