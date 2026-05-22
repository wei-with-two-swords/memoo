+++
title = 'URL 与视图函数'
date = 2026-05-21T17:00:00+08:00
draft = false
categories = ["Django 博客开发", ]
tags = ["django", "博客"]
weight = 10
+++

# 第十章：URL 与视图函数

## Django 的请求处理流程

在第六章中，我们已经初步接触了 URL 配置和视图函数的概念。现在，让我们更深入地理解这两个核心组件。

回顾一下，当用户在浏览器中输入一个 URL 并访问时，Django 的处理流程是这样的：

1. 用户请求到达 Django 应用
2. URL 调度器（URLconf）根据请求的 URL 匹配到对应的视图
3. 视图函数处理请求，可能需要访问数据库
4. 视图返回 HTTP 响应
5. Django 将响应发送回用户的浏览器

在这个流程中，URL 配置和视图函数是最关键的环节。URL 配置决定了哪些 URL 会被哪些视图处理，视图函数则决定了如何处理请求并生成响应。

## Django 的 URL 配置系统

Django 提供了灵活而强大的 URL 配置系统。它使用正则表达式来匹配 URL，将 URL 的各个部分捕获为参数，传递给视图函数。

### URLconf 的工作方式

每个 Django 项目都有一个根 URL 配置，由 settings.py 中的 `ROOT_URLCONF` 设置：

```python
ROOT_URLCONF = 'blog.urls'
```

这告诉 Django，当收到请求时，应该使用 `blog/urls.py` 文件中的配置来处理 URL。

URL 配置本质上是一个 Python 列表，包含了一系列 URL 模式：

```python
# blog/urls.py

from django.urls import path
from article import views

urlpatterns = [
    path('', views.index, name='index'),
    path('article/<int:year>/', views.article_by_year, name='article_by_year'),
]
```

### path() 函数详解

`path()` 是 Django 3.0 引入的新式 URL 配置函数，比之前的 `url()` 函数更简洁易用。

```python
path(route, view, kwargs=None, name=None)
```

**route**：URL 模式字符串，用于匹配请求的 URL。

**view**：对应的视图函数或视图类。

**kwargs**：额外的关键字参数，会传递给视图函数。

**name**：URL 的名称，用于反向解析。

### URL 模式语法

Django 的 URL 模式使用尖括号 `<>` 来定义可捕获的参数。

**基本类型转换器**：

- `<int:name>` - 匹配整数，如 `123`
- `<str:name>` - 匹配字符串（默认），不包括斜杠，如 `hello`
- `<slug:name>` - 匹配 slug 字符串（字母、数字、连字符、下划线），如 `django-getting-started`
- `<path:name>` - 匹配路径，包括斜杠，如 `category/article/`
- `<uuid:name>` - 匹配 UUID，如 `550e8400-e29b-41d4-a716-446655440000`

**自定义转换器**（高级用法）：

如果内置的转换器不能满足需求，你可以创建自定义的转换器：

```python
# blog/converters.py

class FourDigitYearConverter:
    regex = r'\d{4}'
    
    def to_python(self, value):
        return int(value)
    
    def to_url(self, value):
        return '%04d' % value
```

然后注册并使用：

```python
# blog/urls.py

from django.urls import path, register_converter
from . import converters, views

register_converter(converters.FourDigitYearConverter, 'yyyy')

urlpatterns = [
    path('article/<yyyy:year>/', views.article_by_year, name='article_by_year'),
]
```

## 设计博客的 URL 结构

在编写 URL 配置之前，我们需要先设计好博客的 URL 结构。根据需求分析，我们的博客需要以下 URL：

**首页**：`/` - 显示最新文章列表

**文章详情**：`/article/<slug:slug>/` - 显示单篇文章内容

**分类归档**：`/category/<slug:slug>/` - 显示指定分类下的文章

**标签归档**：`/tag/<slug:slug>/` - 显示指定标签下的文章

**搜索**：`/search/` - 显示搜索结果

**Admin**：`/admin/` - 管理员后台

让我们开始编写这些 URL：

```python
# article/urls.py

from django.urls import path
from . import views

app_name = 'article'

urlpatterns = [
    # 首页
    path('', views.index, name='index'),
    
    # 文章详情
    path('article/<slug:slug>/', views.article_detail, name='detail'),
    
    # 分类归档
    path('category/<slug:slug>/', views.category_page, name='category'),
    
    # 标签归档
    path('tag/<slug:slug>/', views.tag_page, name='tag'),
    
    # 搜索
    path('search/', views.search, name='search'),
]
```

注意 `app_name = 'article'` 这行代码，它为这个 URL 配置设置了一个命名空间。这样，在模板中使用 `{% url 'article:index' %}` 就可以明确指定是 article 应用的 index URL，而不会与其他应用的 index 冲突。

## 视图函数基础

视图函数是 Python 函数，接收 `HttpRequest` 对象作为参数，返回 `HttpResponse` 对象。

```python
# article/views.py

from django.http import HttpResponse

def index(request):
    return HttpResponse('Hello, World!')
```

### HttpRequest 对象

当视图函数被调用时，Django 会传递一个 `HttpRequest` 对象作为第一个参数。这个对象包含了请求的所有信息：

**属性**：

- `request.method` - 请求方法，如 'GET'、'POST'
- `request.GET` - GET 请求的参数（QueryDict）
- `request.POST` - POST 请求的参数（QueryDict）
- `request.COOKIES` - 请求携带的 Cookie 字典
- `request.session` - 会话字典
- `request.user` - 当前登录的用户
- `request.path` - 请求的路径，如 '/article/django/'
- `request.get_host()` - 请求的主机名
- `request.get_full_path()` - 请求的完整路径（含查询参数）

**方法**：

- `request.get_host()` - 获取主机名
- `request.get_full_path()` - 获取完整路径
- `request.is_secure()` - 是否使用 HTTPS

### HttpResponse 对象

视图函数必须返回一个 HttpResponse 对象。

```python
from django.http import HttpResponse

def simple_view(request):
    return HttpResponse('Hello!')
```

**常用属性**：

- `content` - 响应的内容（字节字符串）
- `status_code` - HTTP 状态码
- `content_type` - 内容类型，如 'text/html'

**常用方法**：

- `set_cookie(key, value, max_age)` - 设置 Cookie
- `setdefault(key, value)` - 设置默认响应头

### 快捷函数

Django 提供了一些快捷函数，让视图函数更加简洁：

**render()**：

```python
from django.shortcuts import render

def index(request):
    context = {'name': 'World'}
    return render(request, 'article/index.html', context)
```

render() 函数将模板和上下文数据渲染成 HTML 响应，是最常用的快捷函数。

**redirect()**：

```python
from django.shortcuts import redirect

def some_view(request):
    return redirect('article:index')  # 重定向到首页
```

redirect() 函数生成一个重定向响应。

**get_object_or_404()**：

```python
from django.shortcuts import get_object_or_404

def article_detail(request, slug):
    post = get_object_or_404(Post, slug=slug)
    return render(request, 'article/detail.html', {'post': post})
```

get_object_or_404() 函数尝试获取指定条件的对象，如果不存在则返回 404 页面。

## 编写博客视图函数

现在，让我们为博客编写完整的视图函数。

### 首页视图

```python
# article/views.py

from django.shortcuts import render, get_object_or_404
from django.core.paginator import Paginator
from django.conf import settings
from .models import Post


def index(request):
    """博客首页"""
    # 获取所有文章，按创建时间倒序排列
    post_list = Post.objects.all()
    
    # 分页处理
    paginator = Paginator(post_list, settings.ARTICLE_PAGINATE_BY)
    page_number = request.GET.get('page')
    page_obj = paginator.get_page(page_number)
    
    context = {
        'page_obj': page_obj,
        'is_paginated': page_obj.has_other_pages(),
    }
    return render(request, 'article/index.html', context)
```

这里用到了分页功能。Django 内置了 `Paginator` 类来处理分页。我们先获取所有文章，然后按每页文章数切割成多页，从 GET 参数中获取当前页码。

### 文章详情视图

```python
def article_detail(request, slug):
    """文章详情页"""
    post = get_object_or_404(Post, slug=slug)
    
    # 增加浏览量
    post.views += 1
    post.save(update_fields=['views'])
    
    context = {'post': post}
    return render(request, 'article/detail.html', context)
```

### 分类归档视图

```python
def category_page(request, slug):
    """分类页面"""
    category = get_object_or_404(Category, slug=slug)
    post_list = category.posts.all()
    
    paginator = Paginator(post_list, settings.ARTICLE_PAGINATE_BY)
    page_number = request.GET.get('page')
    page_obj = paginator.get_page(page_number)
    
    context = {
        'category': category,
        'page_obj': page_obj,
        'is_paginated': page_obj.has_other_pages(),
    }
    return render(request, 'article/category.html', context)
```

### 标签归档视图

```python
def tag_page(request, slug):
    """标签页面"""
    tag = get_object_or_404(Tag, slug=slug)
    post_list = tag.posts.all()
    
    paginator = Paginator(post_list, settings.ARTICLE_PAGINATE_BY)
    page_number = request.GET.get('page')
    page_obj = paginator.get_page(page_number)
    
    context = {
        'tag': tag,
        'page_obj': page_obj,
        'is_paginated': page_obj.has_other_pages(),
    }
    return render(request, 'article/tag.html', context)
```

### 搜索视图

```python
def search(request):
    """搜索页面"""
    q = request.GET.get('q', '').strip()
    
    if q:
        # 使用 Q 对象进行搜索
        from django.db.models import Q
        post_list = Post.objects.filter(
            Q(title__icontains=q) | Q(content__icontains=q)
        )
    else:
        post_list = Post.objects.none()  # 返回空查询集
    
    paginator = Paginator(post_list, settings.ARTICLE_PAGINATE_BY)
    page_number = request.GET.get('page')
    page_obj = paginator.get_page(page_number)
    
    context = {
        'q': q,
        'page_obj': page_obj,
        'is_paginated': page_obj.has_other_pages(),
    }
    return render(request, 'article/search.html', context)
```

## 添加缺失的 import

确保在 views.py 顶部添加了所有需要的导入：

```python
# article/views.py

from django.shortcuts import render, get_object_or_404
from django.core.paginator import Paginator, EmptyPage, PageNotAnInteger
from django.conf import settings
from django.db.models import Q

from .models import Post, Category, Tag
```

## URL 反向解析

在视图中，我们使用了 `redirect('article:index')` 这样的方式来生成重定向的 URL。这是 URL 的反向解析，也叫反向查找。

为什么要使用反向解析而不是直接写 URL？

1. **解耦**：视图不需要知道 URL 的具体形式，只需要知道 URL 的名称
2. **可维护性**：如果 URL 发生变化，只需要修改 urls.py，不需要修改视图和模板
3. **可读性**：`article:index` 比 `/` 更清晰，表示的是"文章应用的首页"

在模板中也可以使用反向解析：

```html
<a href="{% url 'article:index' %}">首页</a>
<a href="{% url 'article:detail' post.slug %}">文章详情</a>
<a href="{% url 'article:category' category.slug %}">分类页面</a>
```

## 类视图简介

除了函数视图，Django 还支持类视图。类视图提供了更好的代码组织方式和更多的可复用性。

```python
from django.views.generic import ListView, DetailView

class IndexView(ListView):
    model = Post
    template_name = 'article/index.html'
    context_object_name = 'post_list'
    paginate_by = 10

class ArticleDetailView(DetailView):
    model = Post
    template_name = 'article/detail.html'
    context_object_name = 'post'
```

类视图的详细用法我们会在后续章节中介绍。对于简单的视图，使用函数视图更加直观；对于复杂的、复用的视图，类视图更有优势。

## 处理 404 错误

当请求的页面不存在时，Django 会抛出 `Http404` 异常，显示 404 错误页面。

你可以通过 `get_object_or_404()` 函数自动抛出这个异常：

```python
post = get_object_or_404(Post, slug=slug)
```

或者手动抛出：

```python
from django.http import Http404

def my_view(request):
    if not condition:
        raise Http404('Page not found')
```

你还可以自定义 404 错误页面。创建一个 404.html 模板放在 templates 目录下：

```html
<!-- templates/404.html -->

{% extends 'article/base.html' %}

{% block title %}页面未找到{% endblock %}

{% block content %}
<h1>404 - 页面未找到</h1>
<p>抱歉，您访问的页面不存在。</p>
<a href="{% url 'article:index' %}">返回首页</a>
{% endblock %}
```

当发生 404 错误时，Django 会自动使用这个模板。

## 本章小结

在这一章中，我们：

1. **深入理解了 URL 配置系统**：学习了 path() 函数的用法，各种类型转换器，以及如何设计 URL 结构。

2. **设计了博客的 URL 结构**：包括首页、文章详情、分类归档、标签归档、搜索等 URL。

3. **编写了视图函数**：实现了首页、文章详情、分类、标签、搜索等视图。

4. **了解了请求和响应对象**：掌握了 HttpRequest 和 HttpResponse 的常用属性和方法。

5. **学会了 URL 反向解析**：能够在视图和模板中使用 name 来生成 URL。

6. **了解了类视图**：初步认识了 Django 的类视图系统。

URL 和视图是 Django MTV 模式中的核心。接下来的章节中，我们将学习如何创建模板来展示这些视图返回的数据。
