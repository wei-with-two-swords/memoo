+++
title = '用 Admin 管理后台管理数据'
date = 2026-05-21T15:00:00+08:00
draft = false
categories = ["Django 博客开发",]
tags = ["django", "博客"]
weight = 8
+++

# 第八章：用 Admin 管理后台管理数据

## Django 的杀手级功能

很多 Web 框架都需要开发者手动编写后台管理界面，这是一项枯燥但又不得不做的工作。Django 的与众不同之处在于，它自带了一个功能完善、可定制的 Admin 后台管理系统。

当你完成了数据模型的设计后，Django 能够根据模型定义自动生成一个后台管理界面。这个界面支持 CRUD 操作（创建、读取、更新、删除），支持搜索、排序、过滤，还可以通过简单的配置来定制显示内容。

对于博客系统来说，这意味着我们可以几乎零成本地获得一个完整的文章管理后台。只需要在 Admin 后台中创建一些分类、标签，然后撰写文章，就能让博客运行起来。

## 创建超级用户

在访问 Admin 后台之前，我们需要创建一个管理员账户。Django 的认证系统会使用这个账户来控制对后台的访问。

运行以下命令：

```bash
python manage.py createsuperuser
```

系统会提示你输入用户名和密码：

```
Username: admin
Email address: admin@example.com
Password: ********
Password (again): ********
```

输入完成后，超级用户就创建好了。记住这个用户名和密码，我们之后会用到。

如果提示密码强度不够，可以尝试一个更复杂的密码，或者暂时忽略警告（Django 默认会检查密码强度，但在开发环境中可以放宽要求）。

## 访问 Admin 后台

启动开发服务器：

```bash
python manage.py runserver
```

打开浏览器，访问 http://127.0.0.1:8000/admin/。你应该能看到 Django Admin 的登录页面。

输入刚才创建的用户名和密码，点击登录。你会看到 Admin 后台的主界面。

默认情况下，Admin 后台已经为内置应用（Users、Groups）提供了管理界面。我们的 article 应用还没有显示在这里，需要我们手动注册。

## 注册模型到 Admin

在上一章中，我们已经在 article/admin.py 中添加了基本的注册代码。让我再完善一下，让 Admin 后台更加好用。

```python
# article/admin.py

from django.contrib import admin
from .models import Post, Category, Tag


@admin.register(Post)
class PostAdmin(admin.ModelAdmin):
    """文章管理界面"""
    list_display = ['title', 'created_time', 'category', 'views']
    list_filter = ['category', 'created_time', 'tags']
    search_fields = ['title', 'content']
    date_hierarchy = 'created_time'
    ordering = ['-created_time']


@admin.register(Category)
class CategoryAdmin(admin.ModelAdmin):
    """分类管理界面"""
    list_display = ['name', 'slug', 'created_time']
    search_fields = ['name']


@admin.register(Tag)
class TagAdmin(admin.ModelAdmin):
    """标签管理界面"""
    list_display = ['name', 'slug', 'created_time']
    search_fields = ['name']
```

刷新浏览器，你应该能看到 article 应用出现在了 Admin 后台中，展开后可以看到 Post、Category、Tag 三个模型。

## ModelAdmin 选项详解

Django 的 ModelAdmin 提供了大量的配置选项，让你可以精确控制 Admin 界面的外观和行为。让我详细介绍一下我们用到的几个选项。

### list_display

`list_display` 定义了在文章列表页面中显示哪些列。这是一个元组或列表，每个元素是模型的一个字段名或可调用的方法。

```python
list_display = ['title', 'created_time', 'category', 'views']
```

现在，文章列表页面会显示标题、创建时间、分类和浏览量四列。

除了字段名，你还可以添加一个 Python 函数来显示自定义内容：

```python
def admin_thumbnail(self, obj):
    if obj.cover_image:
        return mark_safe(f'<img src="{obj.cover_image.url}" style="width:50px;height:50px;">')
    return '-'

admin_thumbnail.short_description = '封面'
list_display = ['admin_thumbnail', 'title', 'created_time']
```

注意，如果使用函数，需要在函数前加上 `admin_thumbnail.short_description` 来定义列的名称。

### list_filter

`list_filter` 在列表页面的右侧添加一个过滤器栏，允许用户按特定字段筛选数据。

```python
list_filter = ['category', 'created_time', 'tags']
```

添加后，右侧会显示分类、创建时间、标签三个过滤器。点击任意一个值，就会只显示符合条件的数据。

### search_fields

`search_fields` 在列表页面上方添加一个搜索框。

```python
search_fields = ['title', 'content']
```

这会创建一个搜索框，用户输入关键词后，Django 会在 title 和 content 字段中进行搜索。

搜索字段的默认值是区分大小写的模糊匹配。如果需要不区分大小写或精确匹配，可以在字段名前添加前缀：`^` 表示开头匹配，`=` 表示精确匹配，`@` 表示全文搜索（仅支持部分数据库）。

### date_hierarchy

`date_hierarchy` 在列表页面上方添加一个日期导航栏。

```python
date_hierarchy = 'created_time'
```

添加后，页面顶部会出现类似这样的日期导航：2024 > 05 > 20，允许用户快速跳转到特定日期。

### ordering

`ordering` 指定默认的排序方式。

```python
ordering = ['-created_time']
```

`-created_time` 表示按创建时间倒序排列（最新的在最前面）。

### list_per_page

`list_per_page` 指定每页显示多少条数据。

```python
list_per_page = 25
```

默认值是 100。

### actions

`actions` 允许你在列表页面选择多个对象，然后批量执行某个操作。Django 默认提供了一些操作，如删除选中对象。你也可以自定义操作：

```python
def make_published(self, request, queryset):
    queryset.update(status='published')

make_published.short_description = '标记为已发布'

actions = ['make_published']
```

## 编辑页面配置

除了列表页面，Admin 还提供了编辑页面（创建和修改）。你可以通过一些选项来定制这个页面。

### fields 和 fieldsets

`fields` 指定在编辑页面上显示哪些字段，以及它们的排列顺序：

```python
fields = ['title', 'slug', 'category', 'tags', 'content', 'excerpt']
```

`fieldsets` 允许你将字段分组，并给每个组设置标题：

```python
fieldsets = (
    ('基本信息', {
        'fields': ('title', 'slug')
    }),
    ('内容', {
        'fields': ('content', 'excerpt')
    }),
    ('分类与标签', {
        'fields': ('category', 'tags')
    }),
    ('其他', {
        'fields': ('views',),
        'classes': ('collapse',)  # 可折叠
    }),
)
```

使用 `fieldsets` 可以让编辑页面更加清晰，特别是当字段很多的时候。

### readonly_fields

`readonly_fields` 可以将某些字段设为只读，用户不能修改它们：

```python
readonly_fields = ['created_time', 'modified_time']
```

只读字段会以灰色显示，用户无法编辑，但可以看到它们的值。

### prepopulated_fields

`prepopulated_fields` 可以让你在输入一个字段时，自动填充另一个字段的内容。这通常用于 slug 字段：

```python
prepopulated_fields = {'slug': ('title',)}
```

添加这个配置后，用户输入标题时，slug 字段会自动根据标题填充（转换为小写、用中划线替换空格等）。用户也可以手动修改自动生成的 slug。

## 自定义表单字段

有时，内置的字段表单不够满足需求，需要进行自定义验证或使用不同的表单组件。

### formfield_overrides

`formfield_overrides` 可以覆盖某个字段的表单组件：

```python
from django.forms import TextInput, Textarea

class PostAdmin(admin.ModelAdmin):
    formfield_overrides = {
        models.CharField: {'widget': TextInput(attrs={'size': '80'})},
        models.TextField: {'widget': Textarea(attrs={'rows': 20, 'cols': 100})},
    }
```

### 自定义表单类

如果需要更复杂的自定义，可以创建一个自定义的 ModelForm：

```python
from django import forms
from .models import Post

class PostAdminForm(forms.ModelForm):
    class Meta:
        model = Post
        fields = '__all__'
    
    def clean_title(self):
        """自定义验证：标题不能太短"""
        title = self.cleaned_data['title']
        if len(title) < 5:
            raise forms.ValidationError('标题长度不能少于5个字符')
        return title


class PostAdmin(admin.ModelAdmin):
    form = PostAdminForm
```

## 使用搜索和过滤

Admin 后台提供了强大的搜索和过滤功能，让我们来体验一下。

### 创建测试数据

首先，让我们创建一些分类和标签。

1. 点击 "Categories" 旁边的 "Add" 按钮
2. 添加几个分类：
   - 名称：Django，Slug：django
   - 名称：Python，Slug：python
   - 名称：Web开发，Slug：web-development
   - 名称：读书笔记，Slug：reading-notes
3. 点击 "Save" 保存

然后，添加一些标签：
   - 名称：Django教程，Slug：django-tutorial
   - 名称：入门，Slug：getting-started
   - 名称：进阶，Slug：advanced
   - 名称：实战，Slug：practice

### 创建测试文章

现在让我们创建几篇测试文章：

1. 点击 "Posts" 旁边的 "Add" 按钮
2. 填写表单：
   - Title：Django入门教程
   - Slug：django-getting-started
   - Category：选择 Django
   - Tags：选择 Django教程、入门
   - Content：输入一些测试内容
3. 点击 "Save" 保存

多创建几篇文章，这样后续测试列表页面和过滤功能时会更方便。

### 使用 Admin 功能

创建完数据后，让我们来体验 Admin 后台的各种功能：

**查看列表**：在文章列表页面，你可以看到所有文章，每行显示标题、创建时间、分类和浏览量。

**搜索**：在顶部的搜索框输入关键词，可以搜索标题和正文。

**过滤**：右侧的过滤器允许你按分类、创建时间、标签过滤文章。

**日期导航**：点击顶部的时间导航，可以快速跳转到特定日期。

**排序**：点击列标题，可以按该列排序。

**编辑**：点击任意一行，可以进入该文章的编辑页面。

**删除**：勾选文章前的复选框，然后选择顶部的"Delete selected"操作，可以批量删除。

## 完善 Admin 配置

现在，让我们进一步完善 Admin 配置，让它更加好用。

```python
# article/admin.py

from django.contrib import admin
from django.utils.html import format_html
from .models import Post, Category, Tag


@admin.register(Category)
class CategoryAdmin(admin.ModelAdmin):
    list_display = ['name', 'slug', 'post_count', 'created_time']
    search_fields = ['name']
    prepopulated_fields = {'slug': ('name',)}
    
    def post_count(self, obj):
        """显示该分类下的文章数量"""
        return obj.posts.count()
    
    post_count.short_description = '文章数量'


@admin.register(Tag)
class TagAdmin(admin.ModelAdmin):
    list_display = ['name', 'slug', 'post_count', 'created_time']
    search_fields = ['name']
    prepopulated_fields = {'slug': ('name',)}
    
    def post_count(self, obj):
        """显示该标签下的文章数量"""
        return obj.posts.count()
    
    post_count.short_description = '文章数量'


@admin.register(Post)
class PostAdmin(admin.ModelAdmin):
    list_display = ['title', 'category', 'tags_display', 'created_time', 'views']
    list_filter = ['category', 'tags', 'created_time']
    search_fields = ['title', 'content']
    date_hierarchy = 'created_time'
    ordering = ['-created_time']
    prepopulated_fields = {'slug': ('title',)}
    
    fieldsets = (
        ('基本信息', {
            'fields': ('title', 'slug', 'category')
        }),
        ('内容', {
            'fields': ('content', 'excerpt')
        }),
        ('标签', {
            'fields': ('tags',)
        }),
        ('统计', {
            'fields': ('views',),
            'classes': ('collapse',)
        }),
    )
    
    def tags_display(self, obj):
        """显示文章的标签列表"""
        return ', '.join([tag.name for tag in obj.tags.all()])
    
    tags_display.short_description = '标签'
```

这个配置添加了以下改进：

1. 在分类和标签页面显示文章数量
2. 自动根据名称填充 slug
3. 将文章编辑页面分成多个区块
4. 使用 `fieldsets` 让编辑页面更清晰
5. 添加了标签的显示列

## Admin 的进阶功能

Django Admin 还有更多强大的功能，这里简单介绍几个：

### 内联编辑

使用 `InlineModelAdmin`，可以在一个页面中同时编辑相关的对象。比如，在编辑文章时，可以同时添加或编辑标签：

```python
class TagInline(admin.TabularInline):
    model = Post.tags.through
    extra = 1

class PostAdmin(admin.ModelAdmin):
    inlines = [TagInline]
```

### 权限控制

Admin 自带了一套完整的权限系统。你可以：
- 为不同用户分配不同的权限
- 让某些用户只能看不能改
- 记录用户对数据的修改历史

### 动作

自定义 Admin 动作，可以批量执行特定操作：

```python
def mark_featured(self, request, queryset):
    count = queryset.update(is_featured=True)
    self.message_user(request, f'{count} 篇文章已标记为精选')

mark_featured.short_description = '标记为精选文章'

class PostAdmin(admin.ModelAdmin):
    actions = [mark_featured]
```

## 本章小结

在这一章中，我们：

1. **创建了超级用户**：使用 `createsuperuser` 命令创建了管理员账户。

2. **访问了 Admin 后台**：了解了 Admin 后台的基本界面和功能。

3. **注册了模型到 Admin**：将 Post、Category、Tag 模型注册到 Admin 后台。

4. **深入学习了 ModelAdmin 选项**：包括 `list_display`、`list_filter`、`search_fields`、`fieldsets` 等。

5. **体验了 Admin 的各种功能**：搜索、过滤、排序、分页、编辑等。

6. **完善了 Admin 配置**：添加了自定义方法、优化了显示布局、配置了自动填充等。

Django Admin 是它最受欢迎的特性之一。掌握好 Admin 的使用，可以让你的开发效率大幅提升。

下一章，我们将为博客添加富文本编辑器，让文章编辑体验更加友好。在此之前，我们还需要安装一个额外的包来支持富文本编辑。
