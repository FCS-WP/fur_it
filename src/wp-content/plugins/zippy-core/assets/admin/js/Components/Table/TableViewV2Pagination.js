import { Box, Stack, Typography } from '@mui/material'
import React, { useState } from 'react'

const TableViewV2Pagination = ({ rowsPerPage = 10, currentPage = 1, totalRows }) => {
  const startCounter = (currentPage - 1) * rowsPerPage + 1;
  const endCounter = totalRows > (currentPage * rowsPerPage) ? (currentPage * rowsPerPage) : totalRows;
  const totalPages = Math.ceil(totalRows/rowsPerPage);
  return (
    <Box display={'flex'} justifyContent={'end'} p={2}>
        <Stack direction={'row'} spacing={3}>
            <Typography variant='body1'>Rows per page: {rowsPerPage}</Typography>
            <Typography variant='body1'>Current page: {currentPage} / {totalPages}</Typography>
            <Typography variant='body1'>{startCounter} - {endCounter} of {totalRows}</Typography>
        </Stack>
    </Box>
  )
}

export default TableViewV2Pagination