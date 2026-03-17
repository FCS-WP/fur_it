import React, { useState, useEffect } from "react";
import { TextField, Box, MenuItem, Button } from "@mui/material";
import { Api } from "../../../../api/admin";
import { Grid as Grid2 } from "@mui/material";

const ProductFilterbyCategories = ({ onFilter }) => {
  const [categories, setCategories] = useState([]);
  const [filters, setFilters] = useState({
    search: "",
    category: "",
  });

  const isDisabled = !filters.search.trim() && !filters.category;

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const { data } = await Api.categories();
        if (data?.status === "success") {
          setCategories(data.data);
        }
      } catch (err) {
        console.error("Error fetching categories", err);
      }
    };
    fetchCategories();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFilters((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const submitFilters = {
      ...filters,
      category: filters.category === "all" ? "" : filters.category,
    };
    onFilter(submitFilters);
  };

  return (
    <Box sx={{ mb: 2, p: 2, border: "1px solid #eee", borderRadius: 2 }}>
      <Grid2 container size={{ xs: 12, md: 12 }} spacing={2}>
        {/* Search by Name or SKU */}
        <Grid2 size={{ xs: 12, md: 6 }}>
          <TextField
            fullWidth
            label="Search by Name"
            variant="outlined"
            name="search"
            value={filters.search}
            onChange={handleChange}
            className="search-products"
            sx={{ fontSize: "14px" }}
          />
        </Grid2>

        {/* Category Dropdown */}
        <Grid2 size={{ xs: 12, md: 4 }}>
          <TextField
            select
            fullWidth
            label="Category"
            variant="outlined"
            name="category"
            value={filters.category}
            onChange={handleChange}
            sx={{ fontSize: "14px" }}
          >
            <MenuItem autoFocus={true} value="all" sx={{ fontSize: "14px" }}>
              All Categories
            </MenuItem>
            {categories.map((cat) => (
              <MenuItem
                key={cat.term_id}
                sx={{ fontSize: "14px" }}
                value={cat.term_id}
              >
                {cat.name} {`(${cat.count})`}
              </MenuItem>
            ))}
          </TextField>
        </Grid2>

        {/* Submit Button */}
        <Grid2 size={{ xs: 12, md: 2 }} display="flex" alignItems="center">
          <Button
            type="submit"
            variant="contained"
            fullWidth
            onClick={handleSubmit}
            disabled={isDisabled}
          >
            Search
          </Button>
        </Grid2>
      </Grid2>
    </Box>
  );
};

export default ProductFilterbyCategories;
