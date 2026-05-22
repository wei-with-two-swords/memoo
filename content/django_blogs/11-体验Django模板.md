+++
title = '体验 Django 模板'
date = 2026-05-21T18:00:00+08:00
draft = false
categories = ["Django 博客开发", ]
tags = ["django", "博客"]
weight = 11
+++

# 第十一章：体验 Django 模板

## 模板的力量

在前面的学习中，我们已经多次使用过 Django 的模板系统。通过模板，我们可以将 Python 数据转换为 HTML 页面，展示给用户。

Django 的模板系统是一个功能强大且灵活的工具。它使用自己的模板语言，允许在 HTML 中嵌入变量、标签和过滤器，实现动态内容的渲染。

在这一章中，我将带你深入了解 Django 模板系统的各个方面，让你能游刃有余地创建各种复杂的页面。

## 模板配置回顾

在开始之前，让我们回顾一下模板的配置。在 settings.py 中：

```python
TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [BASE_DIR / 'templates'],
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
            ],
        },
    },
]
```

- `DIRS` 指定了额外的模板搜索目录
- `APP_DIRS` 设为 True 后，Django 也会在已安装应用的 templates 目录下搜索模板

## 模板的查找顺序

当你在视图中使用 `render(request, 'article/index.html')` 时，Django 会按照以下顺序查找模板：

1. 如果 DIRS 非空，依次在 DIRS 中的目录查找
2. 在每个已安装应用的 templates 目录下查找
3. 找到第一个匹配的文件就停止

例如，对于模板 `article/index.html`：

- 首先在 `项目根目录/templates/article/index.html` 查找
- 然后在 `article/templates/article/index.html` 查找

如果都找不到，会抛出 `TemplateDoesNotExist` 异常。

## 变量与标签

Django 模板语言包含三种主要元素：**变量**、**标签**和**过滤器**。

### 变量

变量使用双大括号包裹：`{{ variable_name }}`

当模板引擎遇到变量时，会用变量的值替换它。变量可以是：

- Python 对象的属性
- 字典的键
- 列表的索引
- 方法调用的结果

```html
<p>文章标题：{{ post.title }}</p>
<p>作者：{{ post.author.username }}</p>
<p>浏览量：{{ post.views }}</p>
```

如果变量不存在或为 None，模板会显示空字符串，不会抛出错误。

### 标签

标签使用 `{% %}` 包裹，控制模板的行为。

```html
{% if user.is_authenticated %}
    <p>欢迎，{{ user.username }}！</p>
{% else %}
    <p>请 <a href="/login/">登录</a></p>
{% endif %}
```

常用标签：

- `{% if %} / {% elif %} / {% else %} / {% endif %}` - 条件判断
- `{% for %} / {% endfor %}` - 循环
- `{% block %} / {% endblock %}` - 块定义
- `{% extends "base.html" %}` - 模板继承
- `{% include "partial.html" %}` - 包含其他模板
- `{% load %}` - 加载模板标签库
- `{% url %}` - URL 反向解析
- `{% static %}` - 静态文件引用
- `{% csrf_token %}` - CSRF 令牌
- `{% now "Y-m-d" %}` - 当前时间
- `{% firstof %}` - 返回第一个非假值

### 过滤器

过滤器用于修改变量的输出格式，使用管道符 `|` 连接。

```html
<p>{{ name|lower }}</p>
<p>{{ date|date:"Y年m月d日" }}</p>
<p>{{ text|truncatewords:50 }}</p>
```

常用过滤器：

- `lower` / `upper` - 转小写/大写
- `title` - 首字母大写
- `length` - 获取长度
- `default` - 默认值
- `date` - 格式化日期
- `truncatewords` - 截断单词
- `truncatechars` - 截断字符
- `safe` - 标记为安全 HTML
- `striptags` - 移除 HTML 标签
- `add` - 加法
- `slugify` - 转换为 slug

过滤器可以链式使用：

```html
{{ text|lower|truncatewords:20 }}
```

## 模板继承

模板继承是 Django 模板系统最强大的功能之一。它允许你创建一个基础模板，定义页面的整体结构，然后让子模板覆盖或扩展其中的部分。

### 创建基础模板

```html
<!-- templates/article/base.html -->

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}我的博客{% endblock %}</title>
    {% block extra_head %}{% endblock %}
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="{% url 'article:index' %}">我的博客</a>
            </div>
            <ul class="nav-menu">
                <li><a href="{% url 'article:index' %}">首页</a></li>
                <li><a href="{% url 'article:search' %}">搜索</a></li>
            </ul>
        </nav>
    </header>
    
    <main class="container">
        {% block content %}
        <!-- 子模板的内容会放在这里 -->
        {% endblock %}
    </main>
    
    <footer>
        <p>&copy; 2024 我的博客. All rights reserved.</p>
    </footer>
    
    {% block extra_js %}{% endblock %}
</body>
</html>
```

`{% block %}` 标签定义了子模板可以覆盖的区域。基础模板提供了默认内容，但子模板可以提供自己的内容来替换。

### 创建子模板

```html
<!-- templates/article/index.html -->

{% extends "article/base.html" %}

{% block title %}首页 - 我的博客{% endblock %}

{% block content %}
<h1>最新文章</h1>

<div class="article-list">
    {% for post in page_obj %}
    <article class="article-card">
        <h2>
            <a href="{% url 'article:detail' post.slug %}">{{ post.title }}</a>
        </h2>
        <div class="meta">
            <span>分类：<a href="{% url 'article:category' post.category.slug %}">{{ post.category.name }}</a></span>
            <span>发布时间：{{ post.created_time|date:"Y-m-d" }}</span>
            <span>浏览量：{{ post.views }}</span>
        </div>
        <p class="excerpt">{{ post.excerpt }}</p>
        <a href="{% url 'article:detail' post.slug %}" class="read-more">阅读全文</a>
    </article>
    {% empty %}
    <p>暂无文章</p>
    {% endfor %}
</div>

{% include 'article/pagination.html' %}
{% endblock %}
```

`{% extends %}` 标签声明这个模板继承自基础模板。子模板中的所有 `{% block %}` 内容都会替换基础模板中对应的块。

### 模板继承的工作原理

1. 子模板声明继承自基础模板
2. 子模板可以覆盖父模板的块
3. 没有被覆盖的块，使用父模板中的内容
4. 子模板必须只覆盖它需要自定义的部分，其他部分保持原样

## include 标签

`{% include %}` 标签允许在一个模板中嵌入另一个模板。这常用于可复用的页面片段，如分页、导航、页脚等。

```html
{% include 'article/pagination.html' %}
```

包含的模板会使用当前模板的上下文（变量）。你也可以传递额外的变量：

```html
{% include 'article/pagination.html' with page_obj=page_obj %}
```

### 分页模板示例

```html
<!-- templates/article/pagination.html -->

{% if is_paginated %}
<nav class="pagination">
    <ul class="pagination-list">
        {% if page_obj.has_previous %}
        <li><a href="?page=1">首页</a></li>
        <li><a href="?page={{ page_obj.previous_page_number }}">上一页</a></li>
        {% endif %}
        
        <li class="current">第 {{ page_obj.number }} / {{ page_obj.paginator.num_pages }} 页</li>
        
        {% if page_obj.has_next %}
        <li><a href="?page={{ page_obj.next_page_number }}">下一页</a></li>
        <li><a href="?page={{ page_obj.paginator.num_pages }}">末页</a></li>
        {% endif %}
    </ul>
</nav>
{% endif %}
```

## 条件标签

### if 标签

```html
{% if user.is_authenticated %}
    <p>欢迎，{{ user.username }}！</p>
{% elif user.is_staff %}
    <p>欢迎，管理员！</p>
{% else %}
    <p>欢迎，游客！</p>
{% endif %}
```

`if` 标签支持 `and`、`or`、`not` 操作符：

```html
{% if user.is_authenticated and user.is_staff %}
    <p>欢迎，管理员用户！</p>
{% endif %}
```

### for 标签

```html
{% for post in post_list %}
    <li>{{ forloop.counter }}. {{ post.title }}</li>
{% empty %}
    <li>没有文章</li>
{% endfor %}
```

`forloop` 变量提供了循环的状态信息：

- `forloop.counter` - 当前循环次数（从 1 开始）
- `forloop.counter0` - 当前循环次数（从 0 开始）
- `forloop.revcounter` - 剩余循环次数
- `forloop.first` - 是否第一次循环
- `forloop.last` - 是否最后一次循环
- `forloop.parentloop` - 父循环的 forloop 对象

### with 标签

`with` 标签用于缓存复杂的变量，避免重复计算：

```html
{% with post_list.0 as first_post %}
    <p>第一篇文章：{{ first_post.title }}</p>
    <p>浏览量：{{ first_post.views }}</p>
{% endwith %}
```

## URL 反向解析

在模板中使用 `{% url %}` 标签来生成 URL：

```html
<a href="{% url 'article:index' %}">首页</a>
<a href="{% url 'article:detail' post.slug %}">文章详情</a>
<a href="{% url 'article:category' category.slug %}">分类页面</a>
```

`article:index` 中的 `article` 是 URL 配置的命名空间（app_name），`index` 是 URL 的 name。

如果 URL 需要参数，直接在后面添加：

```html
{% url 'article:detail' post.slug %}
```

## 静态文件

在模板中引用静态文件（如 CSS、JavaScript、图片）：

```html
{% load static %}

<link rel="stylesheet" href="{% static 'css/style.css' %}">
<img src="{% static 'images/logo.png' %}" alt="Logo">
```

首先使用 `{% load static %}` 加载静态文件标签库，然后使用 `{% static %}` 标签生成文件路径。

## CSRF 保护

在表单中使用 `{% csrf_token %}` 来防止跨站请求伪造攻击：

```html
<form method="post">
    {% csrf_token %}
    <input type="text" name="q" placeholder="搜索文章">
    <button type="submit">搜索</button>
</form>
```

## 注释

```html
{# 这是单行注释，不会被渲染 #}

{% comment %}
    这是多行注释
    可以写很多行
{% endcomment %}
```

## 自定义模板标签和过滤器

Django 允许你创建自定义的模板标签和过滤器。

### 创建模板标签库

首先，在应用目录下创建一个 templatetags 目录：

```
article/
├── templatetags/
│   ├── __init__.py
│   └── article_tags.py
└── ...
```

在 `__init__.py` 中可以为空，但必须存在。在 `article_tags.py` 中定义标签和过滤器：

```python
# article/templatetags/article_tags.py

from django import template

register = template.Library()


@register.filter
def truncate_chars(text, length):
    """截断文本到指定字符数"""
    if len(text) > length:
        return text[:length] + '...'
    return text


@register.simple_tag
def get_recent_posts(count=5):
    """获取最近的文章"""
    from article.models import Post
    return Post.objects.all()[:count]
```

然后在模板中使用：

```html
{% load article_tags %}

{{ post.content|truncate_chars:100 }}

{% get_recent_posts 5 as recent_posts %}
{% for post in recent_posts %}
    <li>{{ post.title }}</li>
{% endfor %}
```

## 模板中的安全考虑

### 自动转义

默认情况下，Django 会自动转义 HTML 内容，防止 XSS 攻击：

```html
<p>{{ user_input }}</p>
<!-- 如果 user_input 是 "<script>alert('xss')</script>" -->
<!-- 会渲染成 &lt;script&gt;alert('xss')&lt;/script&gt; -->
```

### 安全内容

如果确认内容是安全的，可以使用 `safe` 过滤器：

```html
<p>{{ post.content|safe }}</p>
```

**谨慎使用 `safe` 过滤器！** 只对可信的内容使用，否则可能导致 XSS 攻击。

## 本章小结

在这一章中，我们深入学习了 Django 模板系统的各个方面：

1. **模板查找机制**：了解了 Django 如何在 DIRS 和 APP_DIRS 中查找模板。

2. **变量和标签**：掌握了 `{{ }}` 和 `{% %}` 的基本用法。

3. **过滤器**：学会了使用管道符连接的过滤器来格式化变量。

4. **模板继承**：使用了 `{% extends %}` 和 `{% block %}` 创建可复用的页面结构。

5. **包含模板**：使用了 `{% include %}` 来嵌入可复用的模板片段。

6. **条件标签**：学习了 `{% if %}` 和 `{% for %}` 标签的用法。

7. **URL 反向解析**：在模板中使用了 `{% url %}` 生成 URL。

8. **静态文件**：学会了在模板中引用 CSS 和图片。

9. **CSRF 保护**：了解了 `{% csrf_token %}` 的重要性。

10. **自定义标签**：创建了自定义的模板过滤器和标签。

11. **安全考虑**：了解了自动转义和 safe 过滤器的使用。

这些知识将帮助你在接下来的章节中创建美观的页面模板。
