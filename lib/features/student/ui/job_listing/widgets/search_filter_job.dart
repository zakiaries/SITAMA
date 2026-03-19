import 'package:flutter/material.dart';

class SearchFilterJob extends StatefulWidget {
  final Function(String) onSearch;
  final Function(String) onCategoryFilter;
  final List<String> categories;
  final String selectedCategory;

  const SearchFilterJob({
    Key? key,
    required this.onSearch,
    required this.onCategoryFilter,
    required this.categories,
    required this.selectedCategory,
  }) : super(key: key);

  @override
  State<SearchFilterJob> createState() => _SearchFilterJobState();
}

class _SearchFilterJobState extends State<SearchFilterJob> {
  late TextEditingController _searchController;

  @override
  void initState() {
    super.initState();
    _searchController = TextEditingController();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Search Bar
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: TextField(
            controller: _searchController,
            onChanged: widget.onSearch,
            decoration: InputDecoration(
              hintText: 'Cari posisi atau perusahaan...',
              hintStyle: TextStyle(color: Colors.grey[500], fontSize: 14),
              filled: true,
              fillColor: Colors.white,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.grey[300]!),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.grey[300]!),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Color(0xFF1E3A8A), width: 2),
              ),
              prefixIcon: Icon(Icons.search, color: Colors.grey[400], size: 22),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      icon:
                          Icon(Icons.clear, color: Colors.grey[500], size: 20),
                      onPressed: () {
                        _searchController.clear();
                        widget.onSearch('');
                        setState(() {});
                      },
                    )
                  : null,
              contentPadding:
                  EdgeInsets.symmetric(vertical: 14, horizontal: 12),
            ),
          ),
        ),

        SizedBox(height: 8),

        // Category Filter Chips
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 16),
          child: SizedBox(
            height: 44,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: widget.categories.length,
              separatorBuilder: (_, __) => SizedBox(width: 8),
              itemBuilder: (context, index) {
                final category = widget.categories[index];
                final isSelected = category == widget.selectedCategory;

                return GestureDetector(
                  onTap: () {
                    widget.onCategoryFilter(category);
                  },
                  child: Container(
                    padding: EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    decoration: BoxDecoration(
                      color: isSelected ? Color(0xFF1E3A8A) : Colors.white,
                      border: Border.all(
                        color:
                            isSelected ? Color(0xFF1E3A8A) : Colors.grey[300]!,
                        width: 1.5,
                      ),
                      borderRadius: BorderRadius.circular(24),
                    ),
                    child: Center(
                      child: Text(
                        category,
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: isSelected ? Colors.white : Colors.grey[700],
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ),
        SizedBox(height: 12),
      ],
    );
  }
}
