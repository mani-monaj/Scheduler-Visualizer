for SRC in `find ./ -depth`
do
    DST=`dirname "${SRC}"`/`basename "${SRC}" | tr '[A-Z]' '[a-z]'`
    if [ "${SRC}" != "${DST}" ]
    then
        [ ! -e "${DST}" ] && git mv "${SRC}" "${DST}" || echo "${SRC} was not renamed"
    fi
done
